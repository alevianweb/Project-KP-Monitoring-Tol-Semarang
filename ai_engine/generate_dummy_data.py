import psycopg2
from app.config import DB_CONFIG
from datetime import datetime, timedelta
import random

def generate_dummy_data():
    conn = psycopg2.connect(**DB_CONFIG)
    conn.autocommit = True
    cur = conn.cursor()
    
    print("Mulai membuat data dummy historis yang realistis...")
    
    # Truncate tables to start clean
    try:
        cur.execute("TRUNCATE vehicle_statistics_daily, vehicle_statistics_weekly, vehicle_statistics_monthly RESTART IDENTITY;")
        print("Tabel statistik berhasil dikosongkan.")
    except Exception as e:
        print(f"Gagal truncate tabel: {e}")
    
    # Ambil semua camera
    cur.execute("SELECT id FROM cameras;")
    cameras = [row[0] for row in cur.fetchall()]
    
    today = datetime(2026, 9, 8).date()
    
    for cam_id in cameras:
        # Generate for 75 days back (end of June to early Sep)
        for i in range(75, -1, -1):
            date_obj = today - timedelta(days=i)
            
            base_car = random.randint(1512, 2589)
            base_motor = random.randint(1043, 2011)
            base_bus = random.randint(102, 305)
            base_truck = random.randint(207, 508)
            
            # Month specific adjustments
            if date_obj.month == 7:
                multiplier = 1.15
            elif date_obj.month == 8:
                if date_obj.day == 2:
                    multiplier = 2.0
                elif 14 <= date_obj.day <= 16:
                    multiplier = 2.5
                elif 20 <= date_obj.day <= 23:
                    multiplier = 3.0
                else:
                    multiplier = 1.0
            elif date_obj.month == 9:
                multiplier = 1.5
            else:
                multiplier = 1.0

            car = int(base_car * multiplier)
            motor = int(base_motor * multiplier)
            bus = int(base_bus * multiplier)
            truck = int(base_truck * multiplier)
            
            # Random jitter +/- 10%
            car = random.randint(int(car * 0.9), int(car * 1.1))
            motor = random.randint(int(motor * 0.9), int(motor * 1.1))
            bus = random.randint(int(bus * 0.9), int(bus * 1.1))
            truck = random.randint(int(truck * 0.9), int(truck * 1.1))
            
            total = car + motor + bus + truck
            
            # Daily
            cur.execute("""
                INSERT INTO vehicle_statistics_daily (camera_id, statistic_date, motorcycle, car, bus, truck, total)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
                ON CONFLICT (camera_id, statistic_date) DO NOTHING;
            """, (cam_id, date_obj, motor, car, bus, truck, total))
            
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

    print("Selesai! Data historis (dummy) berhasil ditambahkan sesuai analisis spasial.")
    cur.close()
    conn.close()

if __name__ == '__main__':
    generate_dummy_data()
