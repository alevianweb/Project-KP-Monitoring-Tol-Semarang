import cv2

from app.detector import VehicleDetector

detector = VehicleDetector()

cap = cv2.VideoCapture("videos/test.mp4")

while True:

    ret, frame = cap.read()

    if not ret:
        break

    detections, results = detector.detect(frame)

    for i in range(len(detections.xyxy)):

        x1, y1, x2, y2 = detections.xyxy[i].astype(int)

        class_id = detections.class_id[i]

        class_name = results.names[class_id]

        cv2.rectangle(
            frame,
            (x1, y1),
            (x2, y2),
            (0,255,0),
            2
        )

        cv2.putText(
            frame,
            class_name,
            (x1, y1-10),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.6,
            (0,255,0),
            2
        )

    cv2.imshow("Detection", frame)

    if cv2.waitKey(1) == 27:
        break

cap.release()
cv2.destroyAllWindows()