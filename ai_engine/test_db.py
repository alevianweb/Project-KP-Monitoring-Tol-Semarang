from app.database import get_connection

def test():
    try:
        conn = get_connection()
        print("Database connection successful!")
        conn.close()
    except Exception as e:
        print(f"Database connection failed: {e}")

if __name__ == '__main__':
    test()