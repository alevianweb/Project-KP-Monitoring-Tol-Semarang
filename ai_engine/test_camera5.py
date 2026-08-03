import cv2
import os
import time

from app.database import get_connection
from app.config import FALLBACK_VIDEO
from app.detector import VehicleDetector
from app.tracker import VehicleTracker
from app.counter import VehicleCounter
from api import get_all_cameras

cams = get_all_cameras()
cam = [c for c in cams if c['id'] == 5][0]

print("Initializing for", cam['name'])
detector = VehicleDetector()
tracker = VehicleTracker()
counter = VehicleCounter(
    camera_id=cam["id"],
    line_start_x=cam["line_start_x"],
    line_start_y=cam["line_start_y"],
    line_end_x=cam["line_end_x"],
    line_end_y=cam["line_end_y"]
)

video_path = cam["video_source"]
if not os.path.exists(video_path):
    video_path = os.path.join("..", cam["video_source"])
if not os.path.exists(video_path):
    video_path = FALLBACK_VIDEO

cap = cv2.VideoCapture(video_path)

ret, frame = cap.read()
if not ret:
    print("Cannot read frame")
else:
    print("Frame read successfully")

h, w = frame.shape[:2]
scale_x = w / 1920.0
scale_y = h / 1080.0

ls_x = int(cam["line_start_x"] * scale_x)
ls_y = int(cam["line_start_y"] * scale_y)
le_x = int(cam["line_end_x"] * scale_x)
le_y = int(cam["line_end_y"] * scale_y)

counter.line_start = (ls_x, ls_y)
counter.line_end = (le_x, le_y)

print("Starting detection")
detections, results = detector.detect(frame)
print("Starting tracking")
tracked = tracker.update(detections)
print("Starting counting")
counter.update(tracked, results.names)
print("Success!")
