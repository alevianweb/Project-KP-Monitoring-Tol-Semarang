import psycopg2
from app.database import get_connection

conn = get_connection()
cur = conn.cursor()
cur.execute("SELECT id, name, line_start_x, line_start_y, line_end_x, line_end_y FROM cameras;")
rows = cur.fetchall()
for r in rows:
    print(r)
cur.close()
conn.close()
