import asyncio
from fastapi import FastAPI, HTTPException
from fastapi.responses import StreamingResponse
import cv2
import os
import time
import threading
from contextlib import asynccontextmanager

from app.database import get_connection
from app.config import FALLBACK_VIDEO
from app.detector import VehicleDetector
from app.tracker import VehicleTracker
from app.counter import VehicleCounter
from fastapi.middleware.cors import CORSMiddleware

# Global dictionary to store the latest JPEG frame per camera ID
latest_frames = {}
camera_status = {} # Store the timestamp of the latest frame per camera
camera_density = {} # Store the current number of vehicles on screen

# Global flag to stop threads on shutdown
running = True

# Preload detector to avoid startup latency
print("Loading YOLO Model...")
detector = VehicleDetector()
detector_lock = threading.Lock()
print("YOLO Model Loaded.")

def get_all_cameras():
    try:
        conn = get_connection()
        cur = conn.cursor()
        cur.execute("""
            SELECT id, name, video_source, line_start_x, line_start_y, line_end_x, line_end_y
            FROM cameras WHERE status = TRUE;
        """)
        rows = cur.fetchall()
        cur.close()
        conn.close()
        cams = []
        for row in rows:
            cams.append({
                "id": row[0],
                "name": row[1],
                "video_source": row[2],
                "line_start_x": row[3],
                "line_start_y": row[4],
                "line_end_x": row[5],
                "line_end_y": row[6]
            })
        return cams
    except Exception as e:
        print(f"Error fetching cameras: {e}")
        return []

def update_camera_source_in_db(cam_id, new_url):
    try:
        conn = get_connection()
        cur = conn.cursor()
        cur.execute("UPDATE cameras SET video_source = %s WHERE id = %s;", (new_url, cam_id))
        conn.commit()
        cur.close()
        conn.close()
    except Exception as e:
        print(f"Error updating DB for cam {cam_id}: {e}")

def fetch_pantausemar_stream_url(cam_name):
    # Mapping koordinat CCTV PantauSemar untuk gerbang tol
    mapping = {
        "krapyak": "-6.987779431038806",
        "kaligawe": "-7.0111193118503525",
        "banyumanik": "-7.074309653496582",
        "tembalang": "-7.048618184210592",
        "jatingaleh": "-7.062605567256019"
    }
    lat = mapping.get(cam_name.lower())
    if not lat:
        return None
    try:
        import requests, json
        headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
        r = requests.get('https://pantausemar.semarangkota.go.id/', headers=headers, timeout=6.0)
        if r.status_code == 200:
            text = r.text
            pos = text.find(lat)
            if pos != -1:
                obj_start = text.rfind('{"cctv_id"', 0, pos)
                obj_end = text.find('}]}', pos) + 3
                data = json.loads(text[obj_start:obj_end])
                links = data.get('links', [])
                if links:
                    return links[0].get('url')
    except Exception as e:
        print(f"[{cam_name}] Error auto-fetching PantauSemar: {e}")
    return None

def get_camera_fallback_file(cam_name):
    # Cari rekaman lokal yang sesuai dengan nama kamera
    candidates = [
        os.path.join("datasets", f"{cam_name.lower()}.mp4"),
        os.path.join("datasets", f"{cam_name.lower()}.MP4"),
        os.path.join("..", "datasets", f"{cam_name.lower()}.mp4"),
        os.path.join("..", "datasets", f"{cam_name.lower()}.MP4"),
        FALLBACK_VIDEO,
        os.path.join("..", FALLBACK_VIDEO)
    ]
    for c in candidates:
        if os.path.exists(c):
            return c
    return FALLBACK_VIDEO

def process_camera_loop(cam):
    global running, latest_frames
    
    raw_source = cam["video_source"]
    has_stream_source = str(raw_source).startswith(("http://", "https://", "rtsp://"))
    is_stream = has_stream_source
    fallback_path = get_camera_fallback_file(cam["name"])
    
    video_path = raw_source
    if is_stream:
        print(f"[{cam['name']}] Menghubungkan ke live stream: {video_path}")
        cap = cv2.VideoCapture(video_path)
        ret_test, _ = cap.read() if cap.isOpened() else (False, None)
        
        # Jika link lama gagal (misal 404 token expired), coba auto-resolve link baru dari PantauSemar
        if not ret_test:
            print(f"[{cam['name']}] [WARN] Live stream tidak merespons, mencoba auto-resolve link PantauSemar...")
            fresh_url = fetch_pantausemar_stream_url(cam["name"])
            if fresh_url and fresh_url != video_path:
                print(f"[{cam['name']}] [AUTO-RESOLVER] Menemukan token baru: {fresh_url}")
                cap.release()
                video_path = fresh_url
                raw_source = fresh_url
                cap = cv2.VideoCapture(video_path)
                ret_test, _ = cap.read() if cap.isOpened() else (False, None)
                if ret_test:
                    update_camera_source_in_db(cam["id"], fresh_url)

        if not ret_test:
            print(f"[{cam['name']}] [WARN] Live stream offline/tidak merespons ({video_path}).")
            print(f"[{cam['name']}] Menggunakan rekaman lokal sebagai fallback: {fallback_path}")
            cap.release()
            video_path = fallback_path
            is_stream = False
            cap = cv2.VideoCapture(video_path)
        else:
            print(f"[{cam['name']}] [OK] Berhasil terhubung ke live stream!")
    else:
        if not os.path.exists(video_path):
            video_path = os.path.join("..", cam["video_source"])
        if not os.path.exists(video_path):
            video_path = fallback_path
            print(f"[{cam['name']}] File video tidak ditemukan. Menggunakan fallback: {video_path}")
        cap = cv2.VideoCapture(video_path)
        
    tracker = VehicleTracker()
    # Scale DB coordinates to 800x450 since frame will be resized
    scale_x = 800 / 1920.0
    scale_y = 450 / 1080.0
    counter = VehicleCounter(
        camera_id=cam["id"],
        line_start_x=int(cam["line_start_x"] * scale_x),
        line_start_y=int(cam["line_start_y"] * scale_y),
        line_end_x=int(cam["line_end_x"] * scale_x),
        line_end_y=int(cam["line_end_y"] * scale_y)
    )
    line_color = (0, 0, 255)
    
    print(f"Started AI engine for Camera {cam['name']}...")
    
    # Hitung delay ideal antar frame (misal 40ms untuk 25 FPS)
    fps = cap.get(cv2.CAP_PROP_FPS)
    if fps <= 0 or fps > 60:
        fps = 25.0
    delay = 1.0 / fps
    
    # State Auto-Recovery (pengecekan berkala tiap 60 detik di latar belakang)
    last_recovery_check = time.time()
    recovery_in_progress = False
    recovered_cap = [None]

    def probe_live_stream():
        nonlocal recovery_in_progress, raw_source
        try:
            status_code = 0
            if raw_source.startswith(("http://", "https://")):
                import requests
                try:
                    r = requests.get(raw_source, timeout=4.0)
                    status_code = r.status_code
                except Exception:
                    status_code = 0

            # Jika link 404 (token expired), coba auto-resolve token terbaru dari PantauSemar
            if status_code != 200:
                fresh_url = fetch_pantausemar_stream_url(cam["name"])
                if fresh_url and fresh_url != raw_source:
                    print(f"[{cam['name']}] [AUTO-RESOLVER] Token baru terdeteksi: {fresh_url}")
                    raw_source = fresh_url
                    update_camera_source_in_db(cam["id"], fresh_url)

            test_cap = cv2.VideoCapture(raw_source)
            ret_chk, _ = test_cap.read() if test_cap.isOpened() else (False, None)
            if ret_chk:
                recovered_cap[0] = test_cap
            else:
                test_cap.release()
        except Exception:
            pass
        finally:
            recovery_in_progress = False

    try:
        frame_count = 0
        next_frame_time = time.perf_counter()
        
        last_tracked = None
        last_results = None
        
        # FPS variables
        fps_start_time = time.time()
        fps_counter = 0
        current_fps = 0.0
        
        while running:
            # AUTO-RECOVERY: Jika background probe berhasil mendapatkan kembali live stream
            if recovered_cap[0] is not None:
                print(f"[{cam['name']}] [AUTO-RECOVERY] Live stream PantauSemar kembali online! Beralih kembali ke STREAM: LIVE")
                cap.release()
                cap = recovered_cap[0]
                recovered_cap[0] = None
                video_path = raw_source
                is_stream = True
                new_fps = cap.get(cv2.CAP_PROP_FPS)
                if new_fps > 0 and new_fps <= 60:
                    delay = 1.0 / new_fps
                else:
                    delay = 1.0 / 25.0

            # Cek berkala tiap 60 detik jika saat ini sedang memutar rekaman lokal (fallback)
            if not is_stream and has_stream_source:
                now = time.time()
                if now - last_recovery_check >= 60.0:
                    last_recovery_check = now
                    if not recovery_in_progress:
                        recovery_in_progress = True
                        print(f"[{cam['name']}] [AUTO-RECOVERY] Memeriksa status live stream PantauSemar di background (tiap 60s)...")
                        threading.Thread(target=probe_live_stream, daemon=True).start()

            if not cap.isOpened():
                time.sleep(1)
                cap = cv2.VideoCapture(video_path)
                continue
                
            ret, frame = cap.read()
            if not ret:
                if is_stream:
                    # Beri jeda mikro untuk menunggu segmen chunk HLS berikutnya dari server
                    time.sleep(0.05)
                    ret, frame = cap.read()
                    if not ret:
                        print(f"[{cam['name']}] Stream buffering, mencoba menyambung ulang...")
                        cap.release()
                        cap = cv2.VideoCapture(raw_source)
                        ret_retry, _ = cap.read() if cap.isOpened() else (False, None)
                        if not ret_retry:
                            print(f"[{cam['name']}] [FAILOVER] Live stream terputus! Beralih ke rekaman lokal (fallback): {fallback_path}")
                            cap.release()
                            video_path = fallback_path
                            is_stream = False
                            cap = cv2.VideoCapture(video_path)
                            last_recovery_check = time.time()
                        continue
                else:
                    # Video fallback selesai 1 putaran, loop kembali ke awal
                    cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
                    continue
            
            # SMART FRAME SKIPPING: Baca semua frame agar video mulus, 
            # tapi jalankan YOLO AI hanya setiap 2 frame untuk meringankan GPU.
            frame_count += 1
            fps_counter += 1
            
            # Hitung FPS setiap detik
            if time.time() - fps_start_time >= 1.0:
                current_fps = fps_counter / (time.time() - fps_start_time)
                fps_counter = 0
                fps_start_time = time.time()
            
            frame = cv2.resize(frame, (800, 450))
            h, w = frame.shape[:2]

            # Jalankan deteksi YOLO hanya di frame ganjil atau jika belum ada data
            if frame_count % 2 != 0 or last_tracked is None:
                with detector_lock:
                    detections, results = detector.detect(frame)
                tracked = tracker.update(detections)
                counter.update(tracked, results.names)
                
                last_tracked = tracked
                last_results = results
            else:
                # Gunakan hasil deteksi sebelumnya untuk frame genap
                tracked = last_tracked
                results = last_results
            
            # Draw Polygon Area
            cv2.polylines(frame, [counter.polygon], isClosed=True, color=line_color, thickness=2)
            cv2.putText(frame, "AREA BATAS DETEKSI", (counter.polygon[0][0] + 5, counter.polygon[0][1] - 5),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.45, line_color, 1)
            
            if tracked.tracker_id is not None:
                camera_density[cam['id']] = len(tracked.xyxy)
                for i in range(len(tracked.xyxy)):
                    x1, y1, x2, y2 = tracked.xyxy[i].astype(int)
                    track_id = int(tracked.tracker_id[i])
                    class_id = int(tracked.class_id[i])
                    class_name = results.names[class_id]
                    
                    # Bounding box ramping & bersih (1px) dengan warna Kuning Terang (Amber)
                    # Kuning cerah sangat kontras di aspal siang/malam tanpa menyilaukan
                    box_color = (0, 225, 255)
                    cv2.rectangle(frame, (x1, y1), (x2, y2), box_color, 1)
                    
                    # Teks nama objek dengan teknik "Black Stroke Outline":
                    # Teks putih dengan tepi hitam pekat (seperti subtitle / HUD profesional).
                    # Keunggulan: TIDAK ADA KOTAK BLOK BESAR yang menutupi mobil lain,
                    # dan 100% terbaca jelas di aspal terang (siang), aspal gelap (malam), maupun bodi mobil.
                    label = f"{class_name} #{track_id}"
                    text_y = max(15, y1 - 4)
                    
                    # Layer 1: Garis tepi hitam (tebal 2, anti-aliased)
                    cv2.putText(frame, label, (x1, text_y), cv2.FONT_HERSHEY_SIMPLEX, 0.42, (0, 0, 0), 2, cv2.LINE_AA)
                    # Layer 2: Teks utama putih terang (tebal 1, anti-aliased)
                    cv2.putText(frame, label, (x1, text_y), cv2.FONT_HERSHEY_SIMPLEX, 0.42, (255, 255, 255), 1, cv2.LINE_AA)

            else:
                camera_density[cam['id']] = 0

            overlay_x = 15
            overlay_y = 30
            cv2.rectangle(frame, (overlay_x - 5, overlay_y - 20), (overlay_x + 165, overlay_y + 100), (0, 0, 0), -1)
            
            cv2.putText(frame, f"CCTV: {cam['name']}", (overlay_x, overlay_y),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.45, (255, 255, 255), 1)
            cv2.putText(frame, f"Total Hitung: {counter.total}", (overlay_x, overlay_y + 20),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (255, 255, 255), 1)
            cv2.putText(frame, f"Mobil: {counter.counts.get('car', 0)}", (overlay_x, overlay_y + 35),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (255, 210, 0), 1)
            cv2.putText(frame, f"Motor: {counter.counts.get('motorcycle', 0)}", (overlay_x, overlay_y + 50),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (0, 235, 255), 1)
            cv2.putText(frame, f"Bus/Truk: {counter.counts.get('bus', 0) + counter.counts.get('truck', 0)}", (overlay_x, overlay_y + 65),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (30, 30, 240), 1)
            
            mode_label = "STREAM: LIVE" if is_stream else "STREAM: REKAMAN"
            mode_color = (0, 255, 0) if is_stream else (0, 165, 255)
            cv2.putText(frame, mode_label, (overlay_x, overlay_y + 82),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.35, mode_color, 1)

            # Kompresi JPEG: kualitas 80 agar ukuran file sangat kecil tapi jernih
            ret_enc, buffer = cv2.imencode('.jpg', frame, [int(cv2.IMWRITE_JPEG_QUALITY), 80])
            if ret_enc:
                latest_frames[cam['id']] = buffer.tobytes()
                camera_status[cam['id']] = time.time()
            
            # Pacing presisi tinggi agar video berputar mulus (25 FPS) tanpa tersendat
            next_frame_time += delay
            sleep_time = next_frame_time - time.perf_counter()
            if sleep_time > 0:
                time.sleep(sleep_time)
            elif sleep_time < -0.3: # Jika tertinggal sesaat karena inferensi YOLO
                next_frame_time = time.perf_counter()
                    
    except Exception as e:
        print(f"Error in processing loop for {cam['name']}: {e}")
    finally:
        print(f"Releasing resources for CCTV camera {cam['name']} stream...")
        if cap:
            cap.release()
        if recovered_cap[0]:
            recovered_cap[0].release()

@asynccontextmanager
async def lifespan(app: FastAPI):
    global running
    print("Starting background AI processing loops...")
    cameras = get_all_cameras()
    threads = []
    for cam in cameras:
        # KEMBALI MENGGUNAKAN SINGLE THREAD SEPERTI AWAL
        t = threading.Thread(target=process_camera_loop, args=(cam,), daemon=True)
        t.start()
        threads.append(t)
    yield
    print("Shutting down background loops...")
    running = False
    for t in threads:
        t.join(timeout=2.0)

app = FastAPI(title="Semarang Toll Gate Vehicle Monitoring AI Engine", lifespan=lifespan)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/stream/{camera_id}")
async def stream_camera(camera_id: int):
    # Gunakan Async Generator agar WebGIS menerima frame secepat kilat
    async def generate_frames():
        last_frame = None
        while running:
            if camera_id in latest_frames:
                jpeg_bytes = latest_frames[camera_id]
                
                if jpeg_bytes != last_frame:
                    last_frame = jpeg_bytes
                    yield (b'--frame\r\n'
                           b'Content-Type: image/jpeg\r\n\r\n' + jpeg_bytes + b'\r\n')
                else:
                    await asyncio.sleep(0.005) # Async sleep sangat ringan
            else:
                await asyncio.sleep(0.01)

    return StreamingResponse(generate_frames(), media_type="multipart/x-mixed-replace; boundary=frame")

@app.get("/health")
def health_check():
    return {"status": "healthy", "service": "vehicle_monitoring_ai_engine"}

@app.get("/status")
def get_status():
    current_time = time.time()
    status = {}
    for cam_id, last_time in camera_status.items():
        is_online = (current_time - last_time < 5.0)
        status[cam_id] = {
            "status": "online" if is_online else "offline",
            "vehicles": camera_density.get(cam_id, 0) if is_online else 0
        }
    return status
