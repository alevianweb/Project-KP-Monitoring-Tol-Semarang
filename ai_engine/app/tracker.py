import supervision as sv


class VehicleTracker:

    def __init__(self):

        self.tracker = sv.ByteTrack(
            track_activation_threshold=0.25,
            lost_track_buffer=90
        )

    def update(self, detections):

        return self.tracker.update_with_detections(
            detections
        )