import psycopg2
from app.config import DB_CONFIG

# Kita kembalikan ke garis mendatar (horizontal) seperti semula.
# Berdasarkan kode Anda, garisnya berada di Y=150 (Kaligawe) dan Y=250 (Sisanya) pada layar 800x450.
# Karena video asli ukurannya 1920x1080, kita kalikan (1080/450) = 2.4 untuk mendapatkan nilai Y aslinya.
# Kaligawe = 150 * 2.4 = 360
# Sisanya = 250 * 2.4 = 600

updates = [
    # (Nama Kamera, X_Kiri, Y_Kiri, X_Kanan, Y_Kanan)
    ('Kaligawe', 0, 360, 1920, 600),
    ('Krapyak', 0, 600, 1920, 600),
    ('Jatingaleh', 0, 600, 1920, 600),
    ('Banyumanik', 0, 500, 1920, 600),
    ('Tembalang', 0, 600, 1920, 600)
]

def revert_lines():
    print("Menghubungkan ke database...")
    try:
        conn = psycopg2.connect(**DB_CONFIG)
        cur = conn.cursor()
        for name, start_x, start_y, end_x, end_y in updates:
            cur.execute("""
                UPDATE cameras 
                SET line_start_x = %s, 
                    line_start_y = %s,
                    line_end_x = %s,
                    line_end_y = %s
                WHERE name = %s
            """, (start_x, start_y, end_x, end_y, name))
            print(f"✅ Dikembalikan {name}: garis mendatar di ketinggian Y={start_y}")
            
        conn.commit()
        print("\nSelesai! Database Anda sudah kembali seperti semula.")
    except Exception as e:
        print(f"Error: {e}")
    finally:
        if 'cur' in locals():
            cur.close()
        if 'conn' in locals():
            conn.close()

if __name__ == '__main__':
    revert_lines()
