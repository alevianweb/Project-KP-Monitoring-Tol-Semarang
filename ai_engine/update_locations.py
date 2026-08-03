import psycopg2
from app.config import DB_CONFIG

updates = [
    ('Kaligawe', -6.9731404, 110.4500822),
    ('Krapyak', -6.9904119, 110.3690956),
    ('Jatingaleh', -7.0313342, 110.4211630),
    ('Banyumanik', -7.0657095, 110.4317426),
    ('Tembalang', -7.0496939, 110.4335102)
]

def update_locations():
    conn = psycopg2.connect(**DB_CONFIG)
    cur = conn.cursor()
    for name, lat, lon in updates:
        cur.execute(f"""
            UPDATE cameras 
            SET latitude = %s, 
                longitude = %s,
                geom = ST_SetSRID(ST_MakePoint(%s, %s), 4326)
            WHERE name = %s
        """, (lat, lon, lon, lat, name))
        print(f"Updated {name} to ({lat}, {lon})")
        
    conn.commit()
    cur.close()
    conn.close()

if __name__ == '__main__':
    update_locations()
