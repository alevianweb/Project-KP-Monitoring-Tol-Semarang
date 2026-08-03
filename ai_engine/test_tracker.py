import cv2

from app.detector import VehicleDetector
from app.tracker import VehicleTracker

from app.counter import VehicleCounter

from app.utils import (
    draw_box,
    draw_counting_line,
    draw_counter
)

from app.config import (
    CLASS_NAMES,
    CLASS_COLORS,
    LINE_Y
)

detector = VehicleDetector()

tracker = VehicleTracker()

counter = VehicleCounter(LINE_Y)

cap = cv2.VideoCapture("../datasets/traffic.mp4")

while True:

    ret, frame = cap.read()

    if not ret:
        break

    detections, results = detector.detect(frame)

    tracked = tracker.update(detections)

    total = counter.update(tracked)

    if tracked.tracker_id is not None:

        for i in range(len(tracked.xyxy)):

            x1, y1, x2, y2 = tracked.xyxy[i].astype(int)

            class_id = int(
                tracked.class_id[i]
            )

            track_id = int(
                tracked.tracker_id[i]
            )

            class_name = CLASS_NAMES[class_id]

            color = CLASS_COLORS[class_name]

            label = f"{class_name} ID {track_id}"

            draw_box(
                frame,
                x1,
                y1,
                x2,
                y2,
                color,
                label
            )

    draw_counting_line(
        frame,
        LINE_Y
    )

    draw_counter(
        frame,
        total
    )


    cv2.imshow(
        "Vehicle Tracking",
        frame
    )

    if cv2.waitKey(1) & 0xFF == 27:
        break

cap.release()

cv2.destroyAllWindows()