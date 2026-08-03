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

# Global dictionary to store the latest JPEG frame per camera ID
latest_frames = {}

# Global flag to stop threads on shutdown
running = True

# Preload detector to avoid startup latency
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
    try:
        while running:
            if not cap.isOpened():
                time.sleep(1)
                cap = cv2.VideoCapture(video_path)
                continue
                
            ret, frame = cap.read()
            if not ret:
                cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
                continue
                
            h, w = frame.shape[:2]
            scale_x = w / 1920.0
            scale_y = h / 1080.0
            
            ls_x = int(cam["line_start_x"] * scale_x)
            ls_y = int(cam["line_start_y"] * scale_y)
            le_x = int(cam["line_end_x"] * scale_x)
            le_y = int(cam["line_end_y"] * scale_y)
            
            counter.line_start = (ls_x, ls_y)
            counter.line_end = (le_x, le_y)

            with detector_lock:
                detections, results = detector.detect(frame)
            tracked = tracker.update(detections)
            counter.update(tracked, results.names)
            
            cv2.line(frame, counter.line_start, counter.line_end, line_color, 3)
            cv2.putText(frame, "GARIS BATAS DETEKSI", (counter.line_start[0] + 10, counter.line_start[1] - 10),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.6, line_color, 2)
            
            if tracked.tracker_id is not None:
                for i in range(len(tracked.xyxy)):
                    x1, y1, x2, y2 = tracked.xyxy[i].astype(int)
                    track_id = int(tracked.tracker_id[i])
                    class_id = int(tracked.class_id[i])
                    class_name = results.names[class_id]
                    
                    cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
                    cv2.putText(frame, f"{class_name} #{track_id}", (x1, y1 - 10),
                                cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 0), 2)

            overlay_x = 20
            overlay_y = 45
            cv2.rectangle(frame, (overlay_x - 10, overlay_y - 25), (overlay_x + 240, overlay_y + 110), (0, 0, 0), -1)
            
            cv2.putText(frame, f"CCTV: {cam['name']}", (overlay_x, overlay_y),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)
            cv2.putText(frame, f"Total Hitung: {counter.total}", (overlay_x, overlay_y + 25),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 1)
            cv2.putText(frame, f"Mobil: {counter.counts['car']}", (overlay_x, overlay_y + 45),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 0), 1)
            cv2.putText(frame, f"Motor: {counter.counts['motorcycle']}", (overlay_x, overlay_y + 65),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 255), 1)
            cv2.putText(frame, f"Bus/Truk: {counter.counts['bus'] + counter.counts['truck']}", (overlay_x, overlay_y + 85),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 255), 1)

            ret_enc, buffer = cv2.imencode('.jpg', frame)
            if ret_enc:
                latest_frames[cam['id']] = buffer.tobytes()
            
            time.sleep(0.03) # Prevent CPU starvation
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
        t = threading.Thread(target=process_camera_loop, args=(cam,), daemon=True)
        t.start()
        threads.append(t)
    yield
    print("Shutting down background loops...")
    running = False
    for t in threads:
        t.join(timeout=2.0)

app = FastAPI(title="Semarang Toll Gate Vehicle Monitoring AI Engine", lifespan=lifespan)

@app.get("/stream/{camera_id}")
async def stream_camera(camera_id: int):
    # Endpoint now just reads from the latest_frames buffer
    def generate_frames():
        while running:
            if camera_id in latest_frames:
                jpeg_bytes = latest_frames[camera_id]
                yield (b'--frame\r\n'
                       b'Content-Type: image/jpeg\r\n\r\n' + jpeg_bytes + b'\r\n')
            time.sleep(0.05) # Cap broadcast to ~20 FPS

    return StreamingResponse(generate_frames(), media_type="multipart/x-mixed-replace; boundary=frame")

@app.get("/health")
def health_check():
    return {"status": "healthy", "service": "vehicle_monitoring_ai_engine"}
