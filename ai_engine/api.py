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

def process_camera_loop(cam):
    global running, latest_frames
    
    video_path = cam["video_source"]
    if not os.path.exists(video_path):
        video_path = os.path.join("..", cam["video_source"])
    if not os.path.exists(video_path):
        video_path = FALLBACK_VIDEO
        print(f"Video file not found for camera {cam['name']}. Falling back to: {video_path}")
        
    cap = cv2.VideoCapture(video_path)
    tracker = VehicleTracker()
    counter = VehicleCounter(
        camera_id=cam["id"],
        line_start_x=cam["line_start_x"],
        line_start_y=cam["line_start_y"],
        line_end_x=cam["line_end_x"],
        line_end_y=cam["line_end_y"]
    )
    line_color = (0, 0, 255)
    
    print(f"Started AI engine for Camera {cam['name']}...")
    
    # Hitung delay ideal antar frame (misal 33ms untuk 30 FPS)
    fps = cap.get(cv2.CAP_PROP_FPS)
    if fps == 0 or fps > 120 or fps < 1:
        fps = 30.0
    delay = 1.0 / fps
    
    is_stream = str(video_path).startswith("rtsp://") or str(video_path).startswith("http://")
    
    try:
        frame_count = 0
        next_frame_time = time.perf_counter()
        
        last_tracked = None
        last_results = None
        
        while running:
            if not cap.isOpened():
                time.sleep(1)
                cap = cv2.VideoCapture(video_path)
                continue
                
            ret, frame = cap.read()
            if not ret:
                cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
                continue
            
            # SMART FRAME SKIPPING: Baca semua frame agar video mulus, 
            # tapi jalankan YOLO AI hanya setiap 2 frame untuk meringankan GPU.
            frame_count += 1
            
            frame = cv2.resize(frame, (800, 450))
                
            h, w = frame.shape[:2]
            scale_x = w / 1920.0
            scale_y = h / 1080.0
            
            ls_x = int(cam["line_start_x"] * scale_x)
            ls_y = int(cam["line_start_y"] * scale_y)
            le_x = int(cam["line_end_x"] * scale_x)
            le_y = int(cam["line_end_y"] * scale_y)
            
            counter.line_start = (ls_x, ls_y)
            counter.line_end = (le_x, le_y)

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
            
            cv2.line(frame, counter.line_start, counter.line_end, line_color, 2)
            cv2.putText(frame, "GARIS BATAS DETEKSI", (counter.line_start[0] + 5, counter.line_start[1] - 5),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.45, line_color, 1)
            
            if tracked.tracker_id is not None:
                camera_density[cam['id']] = len(tracked.xyxy)
                for i in range(len(tracked.xyxy)):
                    x1, y1, x2, y2 = tracked.xyxy[i].astype(int)
                    track_id = int(tracked.tracker_id[i])
                    class_id = int(tracked.class_id[i])
                    class_name = results.names[class_id]
                    
                    cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 1)
                    cv2.putText(frame, f"{class_name} #{track_id}", (x1, y1 - 5),
                                cv2.FONT_HERSHEY_SIMPLEX, 0.4, (0, 255, 0), 1)

            else:
                camera_density[cam['id']] = 0

            overlay_x = 15
            overlay_y = 30
            cv2.rectangle(frame, (overlay_x - 5, overlay_y - 20), (overlay_x + 160, overlay_y + 85), (0, 0, 0), -1)
            
            cv2.putText(frame, f"CCTV: {cam['name']}", (overlay_x, overlay_y),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.45, (255, 255, 255), 1)
            cv2.putText(frame, f"Total Hitung: {counter.total}", (overlay_x, overlay_y + 20),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (255, 255, 255), 1)
            cv2.putText(frame, f"Mobil: {counter.counts.get('car', 0)}", (overlay_x, overlay_y + 35),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (0, 255, 0), 1)
            cv2.putText(frame, f"Motor: {counter.counts.get('motorcycle', 0)}", (overlay_x, overlay_y + 50),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (0, 255, 255), 1)
            cv2.putText(frame, f"Bus/Truk: {counter.counts.get('bus', 0) + counter.counts.get('truck', 0)}", (overlay_x, overlay_y + 65),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (0, 0, 255), 1)

            # Kompresi JPEG: kualitas 80 agar ukuran file sangat kecil tapi jernih
            ret_enc, buffer = cv2.imencode('.jpg', frame, [int(cv2.IMWRITE_JPEG_QUALITY), 80])
            if ret_enc:
                latest_frames[cam['id']] = buffer.tobytes()
                camera_status[cam['id']] = time.time()
            
            # Pacing presisi tinggi untuk file video lokal agar 100% mulus (mengatasi limitasi time.sleep di Windows)
            if not is_stream:
                next_frame_time += delay
                sleep_time = next_frame_time - time.perf_counter()
                if sleep_time > 0:
                    time.sleep(sleep_time)
                else:
                    next_frame_time = time.perf_counter() # Reset jika tertinggal
                    
    except Exception as e:
        print(f"Error in processing loop for {cam['name']}: {e}")
    finally:
        print(f"Releasing resources for CCTV camera {cam['name']} stream...")
        if cap:
            cap.release()

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
