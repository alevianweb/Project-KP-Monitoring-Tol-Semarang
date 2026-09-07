import math
import numpy as np
from app.database import get_connection, insert_detection

class VehicleCounter:

    def __init__(self, camera_id, line_start_x, line_start_y, line_end_x, line_end_y):
        self.camera_id = camera_id
        
        # Original database coordinates (fallback)
        self.db_line_start = (line_start_x, line_start_y)
        self.db_line_end = (line_end_x, line_end_y)
        
        self.line_start = self.db_line_start
        self.line_end = self.db_line_end
        
        self.last_position = {}
        self.counted = set()
        
        self.counts = {
            "car": 0,
            "motorcycle": 0,
            "bus": 0,
            "truck": 0
        }
        self.total = 0
        
        # Fixed optimal horizontal line (Y=300 is roughly where zebra crosses are, cars are large and stable)
        self.is_calibrating = False
        
        # Custom optimal Y coordinates for each camera to avoid occlusions and cut-offs
        # (Dihapus agar mengikuti koordinat garis miring dari database)
        self.is_calibrating = False
        
        # Create a polygon buffer around the line
        # Diatur agar sisi bawah lebih tebal/turun (buffer_bottom) daripada sisi atas
        buffer_top = 30    # Ketebalan ke arah atas (pixel)
        buffer_bottom = 90 # Ketebalan ke arah bawah (pixel) - dilebarkan ke bawah
        
        dx = self.line_end[0] - self.line_start[0]
        dy = self.line_end[1] - self.line_start[1]
        length = math.hypot(dx, dy)
        
        if length == 0:
            nx, ny = 0, 1
        else:
            nx = -dy / length
            ny = dx / length
            
        # Asumsi garis digambar dari kiri ke kanan (dx > 0), maka normal (nx, ny) mengarah ke BAWAH
        p1 = (int(self.line_start[0] + nx * buffer_bottom), int(self.line_start[1] + ny * buffer_bottom))
        p2 = (int(self.line_end[0] + nx * buffer_bottom), int(self.line_end[1] + ny * buffer_bottom))
        p3 = (int(self.line_end[0] - nx * buffer_top), int(self.line_end[1] - ny * buffer_top))
        p4 = (int(self.line_start[0] - nx * buffer_top), int(self.line_start[1] - ny * buffer_top))
        
        self.polygon = np.array([p1, p2, p3, p4], np.int32)
        
        # Connect to DB
        self.db_conn = get_connection()

    def _in_polygon(self, pos):
        import cv2
        return cv2.pointPolygonTest(self.polygon, pos, False) >= 0

    def update(self, tracked, class_names):
        if tracked.tracker_id is None:
            return self.counts

        for i in range(len(tracked.xyxy)):
            track_id = int(tracked.tracker_id[i])
            class_id = int(tracked.class_id[i])
            confidence = float(tracked.confidence[i]) if hasattr(tracked, 'confidence') and tracked.confidence is not None else 1.0
            class_name = class_names[class_id]
            
            x1, y1, x2, y2 = tracked.xyxy[i]
            
            # True center of the bounding box (lebih stabil daripada bottom center)
            center_x = int((x1 + x2) / 2)
            center_y = int((y1 + y2) / 2)
            
            current_pos = (center_x, center_y)
            
            # Cek apakah mobil masuk ke dalam Area Polygon
            if self._in_polygon(current_pos):
                if track_id not in self.counted:
                    self.counted.add(track_id)
                    
                    # Determine direction
                    if track_id in self.last_position:
                        prev_pos = self.last_position[track_id]
                        dy = current_pos[1] - prev_pos[1]
                        direction = "Masuk" if dy > 0 else "Keluar"
                    else:
                        direction = "Masuk" # Default
                    
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