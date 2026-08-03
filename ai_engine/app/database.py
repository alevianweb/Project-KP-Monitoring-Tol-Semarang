import psycopg2
from app.config import DB_CONFIG

def get_connection():
    """
    Returns a new PostgreSQL connection using settings from DB_CONFIG.
    """
    return psycopg2.connect(**DB_CONFIG)

def insert_detection(
    conn,
    camera_id,
    vehicle_type,
    direction,
    confidence,
    tracking_id
):
    """
    Inserts a single detection into the vehicle_detections table.
    The database trigger will automatically update daily/weekly/monthly stats.
    """
    try:
        cur = conn.cursor()
        cur.execute(
            """
            INSERT INTO vehicle_detections
            (camera_id, vehicle_type, direction, confidence, tracking_id)
            VALUES (%s, %s, %s, %s, %s)
            """,
            (camera_id, vehicle_type, direction, confidence, tracking_id)
        )
        conn.commit()
    except Exception as e:
        print(f"Error inserting detection to database: {e}")
        conn.rollback()
    finally:
        if 'cur' in locals():
            cur.close()