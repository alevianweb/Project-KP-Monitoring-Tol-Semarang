import psycopg2
from app.config import DB_CONFIG

def check():
    conn = psycopg2.connect(**DB_CONFIG)
    cur = conn.cursor()
    
    # Get tables
    cur.execute("SELECT table_name FROM information_schema.tables WHERE table_schema='public';")
    tables = [row[0] for row in cur.fetchall()]
    print("Tables in database:", tables)
    
    # Check if cameras has data
    if 'cameras' in tables:
        cur.execute("SELECT id, name, location, latitude, longitude, video_source, status FROM cameras;")
        cameras = cur.fetchall()
        print("Camera records:")
        for cam in cameras:
            print(cam)
        
    cur.close()
    conn.close()

if __name__ == '__main__':
    check()
