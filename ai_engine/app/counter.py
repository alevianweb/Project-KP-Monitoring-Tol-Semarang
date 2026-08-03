import math
from app.database import get_connection, insert_detection

class VehicleCounter:

    def __init__(self, camera_id, line_start_x, line_start_y, line_end_x, line_end_y):
        self.camera_id = camera_id
        self.line_start = (line_start_x, line_start_y)
        self.line_end = (line_end_x, line_end_y)
        
        self.last_position = {}
        self.counted = set()
        
        self.counts = {
            "car": 0,
            "motorcycle": 0,
            "bus": 0,
            "truck": 0
        }
        self.total = 0
        
        # Connect to DB
        self.db_conn = get_connection()

    def _ccw(self, A, B, C):
        """Check if three points are listed in a counterclockwise order"""
        return (C[1] - A[1]) * (B[0] - A[0]) > (B[1] - A[1]) * (C[0] - A[0])

    def _intersect(self, A, B, C, D):
        """Return true if line segments AB and CD intersect"""
        return self._ccw(A, C, D) != self._ccw(B, C, D) and self._ccw(A, B, C) != self._ccw(A, B, D)

    def update(self, tracked, class_names):
        if tracked.tracker_id is None:
            return self.counts

        for i in range(len(tracked.xyxy)):
            track_id = int(tracked.tracker_id[i])
            class_id = int(tracked.class_id[i])
            confidence = float(tracked.confidence[i]) if hasattr(tracked, 'confidence') and tracked.confidence is not None else 1.0
            class_name = class_names[class_id]
            
            x1, y1, x2, y2 = tracked.xyxy[i]
            
            # Bottom center of the bounding box
            center_x = int((x1 + x2) / 2)
            center_y = int(y2)
            
            current_pos = (center_x, center_y)

            if track_id in self.last_position:
                prev_pos = self.last_position[track_id]
                
                # Check if the line segment from prev_pos to current_pos intersects the counting line
                if self._intersect(prev_pos, current_pos, self.line_start, self.line_end):
                    if track_id not in self.counted:
                        self.counted.add(track_id)
                        
                        # Determine direction using cross product
                        # Vector of the line
                        line_vec = (self.line_end[0] - self.line_start[0], self.line_end[1] - self.line_start[1])
                        # Vector of the movement
                        move_vec = (current_pos[0] - prev_pos[0], current_pos[1] - prev_pos[1])
                        
                        cross_prod = line_vec[0] * move_vec[1] - line_vec[1] * move_vec[0]
                        direction = "Masuk" if cross_prod > 0 else "Keluar"
                        
                        # Update local counters
                        if class_name in self.counts:
                            self.counts[class_name] += 1
                        self.total += 1
                        
                        # Insert into Database
                        insert_detection(
                            self.db_conn,
                            self.camera_id,
                            class_name,
                            direction,
                            confidence,
                            track_id
                        )

            self.last_position[track_id] = current_pos

        return self.counts