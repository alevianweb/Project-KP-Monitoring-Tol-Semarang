from dotenv import load_dotenv
import os

load_dotenv()

DB_CONFIG = {
    "host": os.getenv("DB_HOST"),
    "port": os.getenv("DB_PORT"),
    "dbname": os.getenv("DB_NAME"),
    "user": os.getenv("DB_USER"),
    "password": os.getenv("DB_PASSWORD"),
}

VIDEO_DIR = os.getenv("VIDEO_DIR", "../datasets")
MODEL_DIR = os.getenv("MODEL_DIR", "../models")
OUTPUT_DIR = os.getenv("OUTPUT_DIR", "./outputs")

FALLBACK_VIDEO = os.path.join(
    VIDEO_DIR,
    "traffic.mp4"
)

CLASS_NAMES = {
    2: "car",
    3: "motorcycle",
    5: "bus",
    7: "truck"
}

CLASS_COLORS = {
    "car": (255, 0, 0),         # biru
    "motorcycle": (0, 255, 255), # kuning
    "bus": (0, 255, 0),          # hijau
    "truck": (0, 0, 255)         # merah
}

LINE_Y = 480