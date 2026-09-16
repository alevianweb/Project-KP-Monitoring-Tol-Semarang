

## Fitur Utama
- **WebGIS CCTV:** Peta spasial interaktif (Leaflet.js) yang memetakan lokasi gerbang tol beserta status kepadatan dan *live stream* CCTV.
- **Command Center:** Pusat kendali visual yang menampilkan *grid* video CCTV dengan *Bounding Box* hasil deteksi AI secara *real-time*, lengkap dengan indikator *Vehicles on Screen*.
- **Dashboard Analisis:** Visualisasi data lalu lintas historis (Chart.js) berbasis agregasi SQL otomatis, memuat komparasi statistik dan tren pertumbuhan kendaraan.

## Teknologi yang Digunakan
- **Frontend / Web Server:** PHP 8.x, Laravel 11, Bootstrap, Leaflet.js, Chart.js
- **Backend / AI Engine:** Python 3.10+, FastAPI, YOLOv11 (Ultralytics), ByteTrack, OpenCV
- **Database:** PostgreSQL 14+ dengan ekstensi spasial **PostGIS**

---

## Persyaratan Sistem (*Prerequisites*)
Sebelum menginstal aplikasi ini, pastikan Anda telah memasang perangkat lunak berikut di komputer Anda:
1. **PHP (>= 8.1)** & **Composer**
2. **Python (>= 3.10)**
3. **PostgreSQL (>= 14)** yang telah diinstal ekstensi **PostGIS**
4. (Opsional namun disarankan) GPU NVIDIA yang mendukung **CUDA** untuk mempercepat pemrosesan *Computer Vision*.

---

## Panduan Instalasi (Langkah demi Langkah)

Proyek ini menggunakan arsitektur *microservices*. Anda harus mengonfigurasi dan menjalankan *Frontend* (Laravel) dan *Backend* (AI) secara terpisah.

### Tahap 1: Konfigurasi Database (PostgreSQL)
1. Buka PostgreSQL (pgAdmin/psql) dan buat *database* baru, misalnya bernama `semarang_toll_eye`.
2. Aktifkan ekstensi PostGIS pada *database* tersebut dengan mengeksekusi kueri SQL:
   ```sql
   CREATE EXTENSION postgis;
   ```

### Tahap 2: Instalasi Frontend Web (Laravel)
1. Buka terminal/CMD, lalu masuk ke folder webgis:
   ```bash
   cd webgis-tol-semarang
   ```
2. Instal semua *library* PHP yang dibutuhkan (folder `vendor` akan otomatis dibuat):
   ```bash
   composer install
   ```
3. Salin konfigurasi *environment*:
   ```bash
   cp .env.example .env
   ```
4. Buka file `.env` menggunakan *teks editor*, lalu ubah konfigurasi *database* menyesuaikan milik Anda:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=semarang_toll_eye
   DB_USERNAME=postgres
   DB_PASSWORD=password_anda
   ```
5. Buat *Application Key* baru:
   ```bash
   php artisan key:generate
   ```
6. Lakukan migrasi untuk membuat tabel-tabel di *database*:
   ```bash
   php artisan migrate
   ```
7. Jalankan *server* peladen web Laravel:
   ```bash
   php artisan serve
   ```
8. Antarmuka WebGIS sekarang bisa diakses di *browser* pada tautan: `http://localhost:8000`

### Tahap 3: Instalasi Backend AI Engine (Python)
*Buka jendela terminal/CMD baru (biarkan terminal Laravel tetap menyala).*
1. Masuk ke folder *AI Engine*:
   ```bash
   cd ai_engine
   ```
2. Buat Lingkungan Virtual Python (agar *library* tidak bentrok dengan aplikasi lain):
   ```bash
   python -m venv venv
   ```
3. Aktifkan Lingkungan Virtual:
   - **Windows:** `venv\Scripts\activate`
   - **Linux/Mac:** `source venv/bin/activate`
4. Instal semua *library* *Machine Learning* yang dibutuhkan (termasuk FastAPI, Ultralytics, psycopg2, dll):
   ```bash
   pip install -r requirements.txt
   ```
5. Salin konfigurasi database (sesuaikan *password* database di dalam file ini jika diperlukan):
   ```bash
   cp .env.example .env
   ```
6. Jalankan mesin *Computer Vision* (FastAPI Server):
   ```bash
   python api.py
   ```
   *Mesin AI sekarang berjalan di latar belakang dan memproses aliran CCTV ke database secara real-time.*

---
**Catatan:** Jika *script* AI dimatikan, antarmuka web (Laravel) akan tetap menyala dan bisa dibuka, namun data deteksi kendaraan tidak akan bertambah dan *stream* di menu Command Center mungkin tidak merespons. Keduanya harus menyala bersamaan untuk performa penuh.


Setelah Itu Jika Menjalankan Frontend (Laravel) dan Backend (Python) pastikan keduanya berjalan pada port yang berbeda
- Frontend (Laravel): http://localhost:8000
- Backend (Python): http://localhost:8001

Baca File aktifkanweb.txt Untuk Menjalankan Keduanya