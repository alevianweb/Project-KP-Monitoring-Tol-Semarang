import requests

def check_stream(cam_id):
    try:
        r = requests.get(f'http://localhost:8001/stream/{cam_id}', stream=True, timeout=5)
        print(f"Status {cam_id}:", r.status_code)
        for chunk in r.iter_content(chunk_size=1024):
            if chunk:
                print(f"Got data from {cam_id}:", len(chunk))
                break
    except Exception as e:
        print(f"Error {cam_id}:", e)

if __name__ == '__main__':
    check_stream(1)
    check_stream(5)
