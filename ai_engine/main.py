detector = VehicleDetector()

tracker = VehicleTracker()

counter = VehicleCounter()

while True:

    detections, results = detector.detect(frame)

    tracked = tracker.update(detections)

    counter.update(tracked)

    draw semua bbox

    draw garis

    draw dashboard