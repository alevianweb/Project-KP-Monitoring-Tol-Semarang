from ultralytics import YOLO
import supervision as sv

class VehicleDetector:

    def __init__(self, model_path="yolo11s.pt"):
        self.model = YOLO(model_path)

    def detect(self, frame):

        results = self.model(
            frame,
            conf=0.40,
            verbose=False
        )[0]

        detections = sv.Detections.from_ultralytics(results)

        allowed = [2, 3, 5, 7]

        mask = [cid in allowed for cid in detections.class_id]

        detections = detections[mask]

        return detections, results