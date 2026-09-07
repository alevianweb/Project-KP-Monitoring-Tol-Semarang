from ultralytics import YOLO
import supervision as sv

class VehicleDetector:

    def __init__(self, model_path="yolo11m.pt"):
        import torch
        self.model = YOLO(model_path)
        
        if torch.cuda.is_available():
            self.model.to('cuda:0')
            device_used = 'CUDA'
        else:
            device_used = 'CPU'
            
        print(f"\n==============================================")
        print(f"🚀 [INFO] YOLO Model berjalan di: {device_used}")
        print(f"==============================================\n")

    def detect(self, frame):

        results = self.model(
            frame,
            conf=0.25,
            verbose=False
        )[0]

        detections = sv.Detections.from_ultralytics(results)

        allowed = [2, 3, 5, 7]

        mask = [cid in allowed for cid in detections.class_id]

        detections = detections[mask]

        return detections, results