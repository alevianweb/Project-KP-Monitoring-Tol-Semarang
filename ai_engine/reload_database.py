import psycopg2
from app.config import DB_CONFIG
import os

def reload_db():
    print("Connecting to PostgreSQL...")
    conn = psycopg2.connect(**DB_CONFIG)
    conn.autocommit = True
    cur = conn.cursor()
    
    # 1. Drop existing tables if they exist to start fresh
    print("Dropping old tables...")
    cur.execute("""
        DROP TRIGGER IF EXISTS trg_vehicle_detection_inserted ON vehicle_detections;
        DROP FUNCTION IF EXISTS update_vehicle_statistics();
        DROP TABLE IF EXISTS vehicle_statistics_monthly;
        DROP TABLE IF EXISTS vehicle_statistics_weekly;
        DROP TABLE IF EXISTS vehicle_statistics_daily;
        DROP TABLE IF EXISTS vehicle_detections;
        DROP TABLE IF EXISTS cameras;
    """)
    
    # 2. Read and execute schema.sql
    schema_path = "../database/schema.sql"
    print(f"Reading schema from {schema_path}...")
    with open(schema_path, "r", encoding="utf-8") as f:
        schema_sql = f.read()
    
    print("Applying schema.sql...")
    cur.execute(schema_sql)
    
    # 3. Read and execute seed.sql
    seed_path = "../database/seed.sql"
    print(f"Reading seeds from {seed_path}...")
    with open(seed_path, "r", encoding="utf-8") as f:
        seed_sql = f.read()
        
    print("Applying seed.sql...")
    cur.execute(seed_sql)
    
    print("Database reloaded successfully!")
    cur.close()
    conn.close()

if __name__ == '__main__':
    reload_db()
