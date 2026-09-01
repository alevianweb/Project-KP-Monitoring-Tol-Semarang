import psycopg2
from app.config import DB_CONFIG
from datetime import datetime, timedelta
import random

def generate_dummy_data():
    conn = psycopg2.connect(**DB_CONFIG)
    conn.autocommit = True
    cur = conn.cursor()
    
    print("Mulai membuat data dummy historis...")
    
    # Ambil semua camera
    cur.execute("SELECT id FROM cameras;")
    cameras = [row[0] for row in cur.fetchall()]
    
    # Generate untuk 30 hari ke belakang
    today = datetime.now()
    
    for cam_id in cameras:
        for i in range(30, 0, -1):
            date_obj = today - timedelta(days=i)
            # Acak jumlah kendaraan
            car = random.randint(150, 400)
            motor = random.randint(100, 300)
            bus = random.randint(10, 50)
            truck = random.randint(20, 80)
            total = car + motor + bus + truck
            
            # Daily
            cur.execute("""
                INSERT INTO vehicle_statistics_daily (camera_id, statistic_date, motorcycle, car, bus, truck, total)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
                ON CONFLICT (camera_id, statistic_date) DO NOTHING;
            """, (cam_id, date_obj.date(), motor, car, bus, truck, total))
            
            # Weekly
            week = date_obj.isocalendar()[1]
            year = date_obj.year
            cur.execute("""
                INSERT INTO vehicle_statistics_weekly (camera_id, week, year, motorcycle, car, bus, truck, total)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                ON CONFLICT (camera_id, week, year) DO UPDATE SET
                motorcycle = vehicle_statistics_weekly.motorcycle + EXCLUDED.motorcycle,
                car = vehicle_statistics_weekly.car + EXCLUDED.car,
                bus = vehicle_statistics_weekly.bus + EXCLUDED.bus,
                truck = vehicle_statistics_weekly.truck + EXCLUDED.truck,
                total = vehicle_statistics_weekly.total + EXCLUDED.total;
            """, (cam_id, week, year, motor, car, bus, truck, total))
            
            # Monthly
            month = date_obj.month
            cur.execute("""
                INSERT INTO vehicle_statistics_monthly (camera_id, month, year, motorcycle, car, bus, truck, total)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                ON CONFLICT (camera_id, month, year) DO UPDATE SET
                motorcycle = vehicle_statistics_monthly.motorcycle + EXCLUDED.motorcycle,
                car = vehicle_statistics_monthly.car + EXCLUDED.car,
                bus = vehicle_statistics_monthly.bus + EXCLUDED.bus,
                truck = vehicle_statistics_monthly.truck + EXCLUDED.truck,
                total = vehicle_statistics_monthly.total + EXCLUDED.total;
            """, (cam_id, month, year, motor, car, bus, truck, total))

    print("Selesai! Data historis (dummy) berhasil ditambahkan.")
    cur.close()
    conn.close()

if __name__ == '__main__':
    generate_dummy_data()
