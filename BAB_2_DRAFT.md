# BAB II LANDASAN TEORI

## 2.1 Tinjauan Studi

Pemantauan volume dan klasifikasi kendaraan secara otomatis pada infrastruktur transportasi telah menjadi fokus riset intensif dalam bidang *Intelligent Transportation System* (ITS) dan *Computer Vision*. Berbagai penelitian terdahulu telah mengkaji efektivitas algoritma *deep learning* untuk deteksi objek, pelacakan multi-target (*multi-object tracking*), serta integrasi data spasial berbasis *Geographic Information System* (GIS). Penelitian-penelitian tersebut menjadi landasan teoretis dan komparatif penting dalam merancang dan mengembangkan sistem **Semarang Toll Eye**, yaitu sistem pemantauan lalu lintas terintegrasi pada lima gerbang tol di Kota Semarang.

Berikut adalah telaah terhadap lima penelitian terdahulu yang memiliki relevansi erat dengan penelitian ini:

1. **Penelitian Hu et al. (2026)** berjudul *"Lane-Based Vehicle Counting System for Complex Traffic Scenes"* membahas permasalahan penghitungan kendaraan per lajur pada skenario lalu lintas padat yang kerap mengalami oklusi (*occlusion*) dan pergerakan kendaraan yang saling menutupi. Penelitian tersebut mengombinasikan model deteksi terbaru YOLOv11 dengan algoritma pelacak ByteTrack. Hasil pengujian menunjukkan bahwa kombinasi YOLOv11 dan ByteTrack mampu mempertahankan konsistensi identitas objek (*track ID*) secara stabil serta menghasilkan akurasi penghitungan volume kendaraan berbasis lajur yang tinggi meskipun pada kondisi kepadatan ekstrem. Penelitian tersebut membuktikan keandalan YOLOv11 dan ByteTrack untuk skenario lalu lintas yang dinamis, namun sistem yang dikembangkan belum diintegrasikan dengan pengelolaan basis data spasial maupun platform pemantauan wilayah berbasis WebGIS.
2. **Penelitian Sherifi et al. (2025)** berjudul *"Intelligent Traffic Monitoring with YOLOv11: A Case Study in Real-Time Vehicle Detection"* mengkaji efektivitas penerapan arsitektur YOLOv11 dalam mendeteksi kendaraan secara *real-time* pada berbagai kondisi pencahayaan dan sudut pandang kamera pengawas. Hasil penelitian menunjukkan variasi akurasi penghitungan kendaraan antara 66,67% hingga 95,83% tergantung pada resolusi video, tingkat kepadatan lalu lintas, dan sudut kemiringan kamera CCTV. Temuan ini menegaskan pentingnya pemilihan titik pemantauan yang tepat serta perlunya penanganan mekanisme pelacakan lanjutan guna mengeliminasi risiko penghitungan ganda (*double counting*).
3. **Penelitian Ng et al. (2023)** berjudul *"Traffic Impact Assessment System using YOLOv5 and ByteTrack"* mengevaluasi dampak lalu lintas perkotaan dengan menguji beberapa algoritma *multi-object tracking* (termasuk SORT, DeepSORT, dan ByteTrack) yang dipasangkan dengan detektor YOLO. Hasil penelitian membuktikan bahwa algoritma ByteTrack mengungguli metode pelacakan lainnya dalam hal kecepatan komputasi dan ketahanan pelacakan, terutama karena ByteTrack memanfaatkan kotak deteksi dengan skor kepercayaan rendah (*low-score detection boxes*) untuk mempertahankan asosiasi objek yang tertutup sebagian. Namun, penelitian ini belum menyediakan visualisasi spasial berbasis peta interaktif dan masih terbatas pada keluaran data statistik lokal.
4. **Penelitian Obaidat & Alomari (2026)** berjudul *"Real-time multi-source AI, computer vision, and GIS fusion for data-driven urban traffic management"* mengusulkan kerangka kerja integratif yang menggabungkan *computer vision*, data sensor ponsel, dan *Geographic Information System* (GIS) untuk manajemen lalu lintas perkotaan secara *real-time*. Penelitian tersebut membuktikan bahwa integrasi antara analitik berbasis kecerdasan buatan (*AI*) dan representasi spasial GIS mampu mempermudah pengambil kebijakan dalam memetakan titik-titik kemacetan perkotaan secara holistik. Akan tetapi, arsitektur yang dibangun memerlukan sumber daya infrastruktur sensor yang sangat kompleks dan belum difokuskan pada pemantauan gerbang tol dengan kamera CCTV publik yang telah terpasang.
5. **Penelitian Chen et al. (2023)** berjudul *"A framework for real-time vehicle counting and velocity estimation using deep learning"* mengembangkan sistem penghitungan kendaraan dan estimasi kecepatan secara *real-time* dengan memadukan detektor YOLOv4 dan pelacak DeepSORT pada kamera jalan raya. Hasil penelitian menunjukkan bahwa sistem mampu melakukan estimasi volume kendaraan dengan tingkat presisi di atas 90%. Kendati demikian, komputasi ekstraksi fitur pada DeepSORT membutuhkan beban pemrosesan GPU yang relatif tinggi, serta belum dilengkapi dengan arsitektur penyimpanan basis data spasial yang mendukung agregasi statistik periodik (harian, mingguan, dan bulanan).

Ringkasan perbandingan kelima penelitian rujukan tersebut disajikan pada Tabel 2.1 sebagai pemetaan *state-of-the-art*.

**Tabel 2.1 State-of-the-art**

| No | Judul, Penulis, & Tahun | Masalah | Metode | Hasil |
| :---: | :--- | :--- | :--- | :--- |
| 1 | *Lane-Based Vehicle Counting System for Complex Traffic Scenes*<br>(Hu et al., 2026) | Penghitungan kendaraan per lajur pada lalu lintas kompleks rentan mengalami salah hitung akibat oklusi kendaraan dan pergantian lajur. | Mengintegrasikan model deteksi objek YOLOv11 dengan algoritma pelacak ByteTrack berbasis batas lajur jalan (*lane-based*). | Berhasil mempertahankan identitas kendaraan secara konsisten pada kondisi lalu lintas padat dan meningkatkan akurasi penghitungan per lajur jalan secara signifikan. |
| 2 | *Intelligent Traffic Monitoring with YOLOv11: A Case Study in Real-Time Vehicle Detection*<br>(Sherifi et al., 2025) | Fluktuasi performa deteksi kendaraan *real-time* akibat variasi sudut pandang kamera, resolusi citra, dan perubahan kondisi cuaca pada rekaman CCTV. | Evaluasi empiris performa arsitektur YOLOv11 untuk deteksi dan klasifikasi multi-kelas kendaraan pada rekaman video lalu lintas. | Memperoleh akurasi penghitungan kendaraan antara 66,67% hingga 95,83%, serta membuktikan efisiensi komputasi YOLOv11 yang lebih unggul dibandingkan generasi sebelumnya. |
| 3 | *Traffic Impact Assessment System using YOLOv5 and ByteTrack*<br>(Ng et al., 2023) | Terjadinya penghitungan ganda (*double counting*) dan hilangnya jejak kendaraan saat kendaraan berhenti atau terhalang kendaraan lain pada analisis dampak lalu lintas. | Kombinasi YOLOv5 untuk deteksi objek dan ByteTrack untuk asosiasi kotak deteksi (*bounding box*) skor tinggi dan rendah. | ByteTrack menghasilkan nilai *Multiple Object Tracking Accuracy* (MOTA) tertinggi dibandingkan algoritma SORT dan DeepSORT dengan konsumsi memori komputasi yang lebih efisien. |
| 4 | *Real-time multi-source AI, computer vision, and GIS fusion for data-driven urban traffic management*<br>(Obaidat & Alomari, 2026) | Ketiadaan integrasi langsung antara hasil pengolahan analitik visual AI dengan visualisasi geografis berbasis peta untuk pemantauan lalu lintas terpadu. | Fusi data multi-sumber menggabungkan pemrosesan *computer vision*, sensor pergerakan, dan platform *Geographic Information System* (GIS). | Menghasilkan platform pemantauan lalu lintas cerdas yang mampu memetakan tingkat kepadatan dan kemacetan secara spasial dan *real-time* di tingkat wilayah perkotaan. |
| 5 | *A framework for real-time vehicle counting and velocity estimation using deep learning*<br>(Chen et al., 2023) | Keterbatasan sistem pemantauan jalan raya dalam menghasilkan data volume dan kecepatan kendaraan secara simultan dan kontinu. | Penggabungan model deteksi YOLOv4, pelacak DeepSORT, dan kalibrasi perspektif geometris kamera jalan raya. | Berhasil menghitung volume dan mengestimasi kecepatan kendaraan secara *real-time* dengan tingkat presisi di atas 90% pada pengujian multi-kamera jalan bebas hambatan. |

Berdasarkan pemetaan *state-of-the-art* pada Tabel 2.1, tampak jelas adanya kesenjangan penelitian (*research gap*). Penelitian terdahulu umumnya berfokus pada salah satu aspek secara terpisah, yakni antara optimalisasi akurasi deteksi dan pelacakan kendaraan di tingkat algoritma (Hu et al., 2026; Ng et al., 2023; Sherifi et al., 2025) atau perancangan arsitektur fusi data spasial skala makro (Obaidat & Alomari, 2026). Belum ditemukan penelitian yang secara khusus mengintegrasikan alur pemrosesan video *real-time* menggunakan **YOLOv11** dan **ByteTrack**, penghitungan berbasis area perlintasan poligonal (*polygon crossing*), pengelolaan basis data spasial relasional **PostgreSQL/PostGIS**, serta penyajian informasi interaktif melalui portal **WebGIS** yang mencakup pemantauan multi-kamera, indikator status kepadatan, dan statistik agregasi temporal (harian, mingguan, dan bulanan) pada titik gerbang tol jalan bebas hambatan di Kota Semarang. Kesenjangan inilah yang dijembatani oleh penelitian ini melalui pengembangan sistem terintegrasi **Semarang Toll Eye**.

---

## 2.2 Tinjauan Pustaka

### 2.2.1 Sistem Pemantauan Lalu Lintas Cerdas (*Intelligent Transportation System*)
Sistem Transportasi Cerdas (*Intelligent Transportation System* / ITS) merupakan integrasi teknologi informasi, telekomunikasi, dan kecerdasan komputasional yang diaplikasikan pada prasarana transportasi untuk meningkatkan keamanan, kelancaran mobilitas, dan efisiensi manajemen lalu lintas (Wijaya, 2026). Pada area gerbang tol (*toll plaza*), sistem pemantauan bertugas mengawasi lalu lintas kendaraan yang masuk dan keluar, mendeteksi potensi antrean, serta menyediakan data kuantitatif mengenai volume pergerakan kendaraan berdasarkan kelas atau golongannya (Ichwan & Arifin, 2022). Penerapan otomatisasi pada ITS memungkinkan pengumpulan data lalu lintas berjalan secara berkelanjutan (*continuous real-time monitoring*) tanpa membebani petugas pengamat lapangan secara fisik.

### 2.2.2 Dinas Komunikasi dan Informatika Digital (Diskomdigi) Provinsi Jawa Tengah
Dinas Komunikasi dan Informatika Digital (Diskomdigi) Provinsi Jawa Tengah merupakan unsur pelaksana urusan pemerintahan daerah yang membidangi urusan komunikasi, informatika, statistik, dan persandian, yang beralamat di Jl. Menteri Supeno No. 2, Mugassari, Kecamatan Semarang Selatan, Kota Semarang, Jawa Tengah. Instansi ini memiliki peran strategis dalam merumuskan kebijakan teknis, mengoordinasikan implementasi Sistem Pemerintahan Berbasis Elektronik (SPBE), mengelola inisiatif Satu Data Jawa Tengah, serta mewujudkan konsep *Smart Province* dan *Smart City*.

Salah satu infrastruktur penting yang dinaungi dan diintegrasikan oleh Diskomdigi Jawa Tengah bersama Dinas Perhubungan adalah jaringan kamera pengawas (*Closed-Circuit Television* / CCTV) perkotaan dan fasilitas publik, seperti platform PantauSemar. Keberadaan jaringan kamera CCTV publik yang tersebar di titik-titik strategis Kota Semarang—termasuk persimpangan dan akses gerbang tol—menyediakan aliran siaran visual secara daring. Melalui kerja sama magang di Diskomdigi Provinsi Jawa Tengah, penelitian ini memanfaatkan aliran siaran CCTV tersebut sebagai data masukan utama untuk diolah menjadi data terstruktur yang berdaya guna bagi tata kelola transportasi perkotaan.

### 2.2.3 *Computer Vision* dan Pengolahan Citra Digital
*Computer Vision* adalah cabang ilmu kecerdasan buatan (*Artificial Intelligence*) yang berfokus pada pengembangan algoritma komputer agar mampu meniru kemampuan visual manusia dalam memahami, menganalisis, dan mengekstraksi informasi bermakna dari citra diam maupun rangkaian frame video digital. Citra digital direpresentasikan sebagai matriks dua dimensi $f(x, y)$ yang memuat nilai intensitas piksel pada koordinat spasial tertentu. Dalam sistem pemrosesan video lalu lintas, aliran video digital dipecah menjadi rangkaian citra berurutan (*frames*) dengan laju tertentu (*frame rate* / FPS). Tahapan pra-pengolahan citra meliputi penyesuaian dimensi frame (*resizing*), konversi ruang warna, serta perbaikan kontras untuk memastikan data visual dapat diproses secara optimal oleh model jaringan syaraf tiruan (*neural network*).

### 2.2.4 Deteksi Objek Berbasis *Deep Learning* dan Arsitektur YOLOv11
Deteksi objek (*object detection*) merupakan tugas fundamental *computer vision* yang bertujuan menentukan lokasi objek di dalam citra (lokalisasi) menggunakan kotak pembatas (*bounding box*) sekaligus menetapkan kelas objek tersebut (klasifikasi). Pendekatan *You Only Look Once* (YOLO) yang diperkenalkan oleh Redmon et al. (2016) merevolusi metode deteksi objek dengan mengonsepkan deteksi sebagai masalah regresi tunggal dari piksel citra langsung ke koordinat kotak pembatas dan probabilitas kelas (*single-stage detector*). Hal ini membuat model YOLO memiliki kecepatan inferensi yang sangat tinggi sehingga sangat cocok untuk pemrosesan *real-time*.

YOLOv11 merupakan arsitektur generasi termutakhir yang dirilis oleh Ultralytics pada akhir tahun 2024 (Khanam & Hussain, 2024). YOLOv11 memperkenalkan serangkaian penyempurnaan struktural dibanding pendahulunya (YOLOv8):
1. **Penyempurnaan Arsitektur *Backbone* dan *Neck*:** Mengadopsi modul C3k2 (*Cross Stage Partial block with kernel size 2*) yang lebih efisien dalam mengekstraksi fitur visual tanpa meningkatkan beban parameter komputasi.
2. **Integrasi Perhatian Spasial C2PSA (*Cross-Stage Partial with Spatial Attention*):** Memperkuat kemampuan model dalam menangkap relasi kontekstual antar objek pada citra yang padat, sehingga akurasi lokalisasi kendaraan berukuran kecil atau yang saling bertumpuk meningkat secara signifikan.
3. **Peningkatan *Spatial Pyramid Pooling - Fast* (SPPF):** Mengoptimalkan penggabungan representasi fitur multi-skala sehingga model tangguh mengenali kendaraan dengan sudut perspektif kamera yang miring (*oblique viewpoint*).

Pada penelitian ini, model YOLOv11 digunakan untuk mendeteksi dan mengklasifikasikan empat kategori kendaraan: Mobil Pribadi (*car*), Sepeda Motor (*motorcycle*), Bus (*bus*), dan Truk (*truck*).

### 2.2.5 Algoritma Pelacakan Objek Multi-Target (*ByteTrack*)
Model deteksi objek melakukan inferensi frame per frame secara independen tanpa mengingat riwayat frame sebelumnya. Akibatnya, satu kendaraan yang sama akan terdeteksi berulang kali di setiap frame, memicu risiko penghitungan ganda (*double counting*). Untuk mengatasi masalah tersebut, dibutuhkan algoritma pelacakan multi-target (*Multi-Object Tracking* / MOT).

ByteTrack (Zhang et al., 2022) merupakan algoritma pelacakan berbasis paradigma *Tracking-by-Detection* yang sangat efisien dan akurat. Keunggulan utama ByteTrack dibandingkan algoritma pelacak lain (seperti SORT dan DeepSORT) terletak pada metode asosiasinya yang bernama **BYTE**:
* Algoritma pelacak konvensional biasanya hanya mengasosiasikan kotak deteksi yang memiliki skor kepercayaan tinggi (*high confidence score*, misal $\ge 0.5$) dan langsung membuang deteksi dengan skor rendah. Padahal, pada kondisi lalu lintas padat atau saat terjadi oklusi sebagian (*occlusion*), skor deteksi kendaraan dapat turun (misal menjadi $0.2 - 0.4$). Membuang deteksi ini menyebabkan jejak objek terputus (*identity switch / track loss*).
* ByteTrack membagi deteksi menjadi dua kelompok: skor tinggi dan skor rendah. Pada tahap pertama, deteksi skor tinggi diasosiasikan dengan lintasan jejak (*tracklets*) menggunakan jarak *Intersection over Union* (IoU) dan algoritma Hungarian. Pada tahap kedua, lintasan jejak yang belum terpasangkan diasosiasikan kembali dengan deteksi skor rendah. Dengan cara ini, kendaraan yang terhalang tiang rambu, pohon, atau kendaraan besar di sampingnya tetap dapat dipertahankan identitas uniknya (*track ID*) secara konsisten.

Prediksi posisi pergerakan kotak pembatas antar-frame dalam ByteTrack diestimasi menggunakan *Kalman Filter*, yang memodelkan pergerakan linear kendaraan berdasarkan posisi spasial $(x, y)$, rasio aspek $(s)$, ketinggian $(h)$, serta kecepatan perubahan masing-masing variabel.

### 2.2.6 Penghitungan Kendaraan (*Vehicle Counting*) dan Batas Area Poligon
Proses penghitungan kendaraan otomatis dilakukan dengan mendeteksi momen ketika lintasan pusat (*centroid*) kotak pembatas suatu kendaraan bernomor identitas (*track ID*) melewati garis virtual atau batas poligon yang telah ditentukan pada frame CCTV.

Pada penelitian ini, alih-alih menggunakan garis lurus satu dimensi (*single line*) yang rentan terlewat akibat fluktuasi frame (*frame skipping*), diterapkan mekanisme **Polygon Crossing Area (Area Batas Deteksi)**. Poligon didefinisikan sebagai sekumpulan koordinat verteks $P = \{(x_1, y_1), (x_2, y_2), \dots, (x_n, y_n)\}$. Titik pusat kendaraan dihitung melalui:
$$x_c = \frac{x_1 + x_2}{2}, \quad y_c = \frac{y_1 + y_2}{2}$$

Pengujian apakah titik pusat $(x_c, y_c)$ berada di dalam poligon dihitung menggunakan uji spasial *Ray Casting / Point-in-Polygon*. Saat sebuah kendaraan dengan *track ID* tertentu terdeteksi masuk dan melintasi zona poligon batas, sistem memicu peristiwa pencatatan (*counting event*), menambah akumulator statistik sesuai jenis kendaraan, dan menandai ID tersebut agar tidak dihitung berulang kali (*anti-double counting*).

### 2.2.7 Basis Data Spasial PostgreSQL dan Ekstensi PostGIS
PostgreSQL merupakan sistem manajemen basis data relasional (*Relational Database Management System* / RDBMS) tingkat lanjut berbasis *open source* yang memiliki keandalan tinggi, kepatuhan ACID (*Atomicity, Consistency, Isolation, Durability*), dan dukungan konkurensi data berskala besar.

PostGIS adalah ekstensi spasial untuk PostgreSQL yang menambahkan dukungan tipe data geografis (geometri dan geografi seperti *Point*, *LineString*, dan *Polygon*) serta fungsi analisis spasial berbasis standar konsorsium *Open Geospatial Consortium* (OGC). Dalam penelitian ini, PostGIS digunakan untuk menyimpan koordinat spasial titik gerbang tol Kota Semarang menggunakan sistem koordinat referensi WGS 84 (EPSG:4326):
```sql
geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
```
Selain menyimpan koordinat geografis kamera gerbang tol, PostgreSQL digunakan untuk menampung riwayat data perlintasan kendaraan (*vehicle detections*) serta tabel agregasi statistik periodik (*vehicle statistics daily, weekly, monthly*) untuk mendukung analisis tren volume lalu lintas.

### 2.2.8 Sistem Informasi Geografis Berbasis Web (*WebGIS*) dan *Leaflet.js*
*WebGIS* adalah integrasi antara Sistem Informasi Geografis (SIG) dengan arsitektur web modern yang memungkinkan pengguna mengakses, menampilkan, menganalisis, dan memanipulasi data bereferensi spasial melalui peramban web (*web browser*) tanpa memerlukan perangkat lunak SIG desktop khusus (Li & Li, 2022).

Dalam penelitian ini, visualisasi spasial interaktif di sisi *front-end* dibangun menggunakan pustaka JavaScript sumber terbuka **Leaflet.js**. Leaflet menyediakan komponen pemetaan interaktif yang ringan, responsif, dan kaya fitur, seperti pemberian penanda kustom (*custom marker icons*), jendela sembul (*popup* interaktif) yang menyematkan siaran langsung kamera CCTV, serta visualisasi lapisan batas wilayah dan jaringan jalan tol Kota Semarang menggunakan format GeoJSON.

### 2.2.9 Framework dan Alat Pengembangan Sistem
Sistem yang dikembangkan menerapkan arsitektur *microservice* terpisah yang terdiri dari:
1. **Python dan OpenCV:** Digunakan untuk menangani pembacaan aliran video, manipulasi matriks citra, pemformatan teks visual, kompresi frame MJPEG, serta antarmuka ke pustaka *Supervision* dan model YOLOv11.
2. **FastAPI:** Kerangka kerja web berkinerja tinggi berbasis Python yang menggunakan konsep *asynchronous programming* (`async`/`await`). FastAPI berperan sebagai *AI Inference Engine Service* yang memproses deteksi di latar belakang (*background threading*) dan menyediakan *endpoint* *streaming* frame MJPEG berkecepatan tinggi ke antarmuka pengguna.
3. **Laravel:** Kerangka kerja aplikasi web berbasis PHP dengan pola arsitektur *Model-View-Controller* (MVC). Laravel bertindak sebagai portal utama WebGIS yang mengelola autentikasi, penyajian peta interaktif, kueri data spasial PostgreSQL/PostGIS melalui ORM Eloquent, serta perenderan antarmuka dashboard analitik statistik.
4. **Chart.js:** Pustaka visualisasi data berbasis JavaScript yang digunakan pada dashboard analitik untuk menyajikan diagram tren volume kendaraan harian, mingguan, dan bulanan secara interaktif dan responsif.

### 2.2.10 Metode Pengembangan Perangkat Lunak (*Waterfall*) dan Pemodelan UML
Metode pengembangan sistem yang digunakan dalam penelitian ini adalah metode **Waterfall** (Pressman, 2015). Metode ini menerapkan pendekatan terstruktur dan sekuensial linear, yang mencakup lima tahapan utama:
1. **Analisis Kebutuhan (*Requirements Analysis*):** Mengidentifikasi kebutuhan fungsional dan non-fungsional sistem pemantauan gerbang tol, ketersediaan aliran data CCTV PantauSemar, serta parameter data statistik kendaraan yang dibutuhkan.
2. **Desain Sistem (*System Design*):** Merancang arsitektur sistem, skema basis data spasial (ERD), alur proses deteksi dan pelacakan, serta antarmuka pengguna WebGIS menggunakan notasi UML.
3. **Implementasi (*Coding / Implementation*):** Mengembangkan mesin kecerdasan buatan (*AI engine*) menggunakan Python/FastAPI/YOLOv11 dan membangun aplikasi WebGIS menggunakan framework Laravel dan Leaflet.js.
4. **Pengujian Sistem (*Testing*):** Menguji akurasi model deteksi dan penghitungan kendaraan serta menguji fungsi antarmuka sistem secara keseluruhan menggunakan pengujian *Black Box*.
5. **Pemeliharaan (*Maintenance*):** Melakukan penyesuaian parameter dan pembaruan sistem sesuai dengan evaluasi pengoperasian.

Untuk mendokumentasikan dan memodelkan rancangan sistem berorientasi objek, digunakan *Unified Modeling Language* (UML) yang mencakup:
* **Use Case Diagram:** Menggambarkan interaksi antara aktor (pengguna/pengelola) dengan fungsi-fungsi sistem. Simbol standar Use Case Diagram disajikan pada Tabel 2.2.
* **Activity Diagram:** Memodelkan alur kerja (*workflow*) fungsional sistem, mulai dari inisialisasi pembacaan CCTV, proses inferensi YOLOv11, pelacakan ByteTrack, penghitungan poligon, hingga penyimpanan ke basis data. Simbol standar Activity Diagram disajikan pada Tabel 2.3.
* **Sequence Diagram:** Menggambarkan interaksi antar-objek dan komponen perangkat lunak berdasarkan urutan waktu. Simbol standar Sequence Diagram disajikan pada Tabel 2.4.
* **Class Diagram:** Menggambarkan struktur kelas basis data, atribut, metode, dan relasi logis antar-tabel dalam sistem. Simbol standar Class Diagram disajikan pada Tabel 2.5.

**Tabel 2.2 Simbol-Simbol Use Case Diagram**

| Simbol | Nama Simbol | Keterangan |
| :---: | :---: | :--- |
| <img src="" width="40"/> | *Actor* | Entitas eksternal (manusia atau sistem lain) yang berinteraksi langsung dengan sistem. |
| ⬭ | *Use Case* | Representasi fungsionalitas atau layanan yang disediakan sistem untuk aktor. |
| ─── | *Association* | Menghubungkan aktor dengan use case yang dapat diaksesnya. |
| «include» | *Include* | Hubungan dependensi yang menunjukkan bahwa suatu use case secara mutlak menyertakan use case lain. |
| «extend» | *Extend* | Hubungan kondisional di mana suatu use case dapat memperluas perilaku use case lainnya dalam kondisi tertentu. |

**Tabel 2.3 Simbol-Simbol Activity Diagram**

| Simbol | Nama Simbol | Keterangan |
| :---: | :---: | :--- |
| ● | *Initial State* | Menandakan titik awal dimulainya suatu alur aktivitas. |
| ▭ (rounded) | *Activity / Action* | Menggambarkan proses kerja atau tindakan yang sedang dieksekusi oleh sistem. |
| ◇ | *Decision* | Titik percabangan kondisi logis yang menghasilkan satu dari beberapa alternatif alur (*true/false*). |
| ❚ | *Fork / Join* | Memisahkan satu aliran menjadi beberapa aliran paralel (*fork*) atau menggabungkan kembali aliran paralel (*join*). |
| ◉ | *Final State* | Menandakan akhir dari seluruh rangkaian aktivitas dalam diagram. |

**Tabel 2.4 Simbol-Simbol Sequence Diagram**

| Simbol | Nama Simbol | Keterangan |
| :---: | :---: | :--- |
| ▭ | *Lifeline* | Menggambarkan keberadaan suatu objek atau komponen selama rangkaian interaksi berlangsung. |
| ┃ | *Activation Bar* | Menunjukkan periode waktu di mana suatu objek sedang aktif mengeksekusi operasi. |
| ──> | *Synchronous Message* | Pengiriman pesan di mana pemanggil menunggu balasan sebelum melanjutkan eksekusi. |
| - - -> | *Reply / Return Message* | Pengiriman nilai balik atau hasil pemrosesan kepada pemanggil pesan. |

**Tabel 2.5 Simbol-Simbol Class Diagram**

| Simbol | Nama Simbol | Keterangan |
| :---: | :---: | :--- |
| ┌──────┐<br>│ Class  │<br>├──────┤<br>│ Atribut│<br>├──────┤<br>│ Operasi│<br>└──────┘ | *Class Box* | Menggambarkan struktur kelas yang terdiri dari nama kelas, daftar atribut data, dan daftar operasi/metode. |
| ─── | *Association* | Menunjukkan relasi struktural statis antar-kelas. |
| ◇─── | *Aggregation* | Relasi "memiliki" (*has-a*) di mana siklus hidup kelas bagian tidak terikat mutlak pada kelas induk. |
| ◆─── | *Composition* | Relasi kepemilikan mutlak di mana keberadaan kelas bagian bergantung penuh pada kelas induk. |
| ──▷ | *Generalization* | Hubungan pewarisan (*inheritance*) dari kelas induk ke kelas anak. |

### 2.2.11 Pengujian Sistem (*Testing*)
Pengujian sistem dilakukan untuk menjamin kebenaran fungsi perangkat lunak serta mengukur kinerja model kecerdasan buatan secara kuantitatif:
1. **Pengujian Fungsionalitas (*Black Box Testing*):**
   Pengujian perangkat lunak yang berfokus pada pengujian fungsionalitas antarmuka dan *endpoint* sistem tanpa melihat struktur kode internal program. Pengujian memvalidasi kesesuaian masukan (*input*) dengan keluaran (*output*) pada portal WebGIS, pemutaran siaran langsung, pergantian opsi periode data statistik, dan interaksi peta.
2. **Evaluasi Metrik Deteksi Objek (*Computer Vision Metrics*):**
   Kinerja model YOLOv11 diukur menggunakan metrik standar evaluasi deteksi objek:
   * *Precision* ($P$): Mengukur proporsi deteksi kendaraan yang benar dari total seluruh deteksi yang dihasilkan model:
     $$P = \frac{TP}{TP + FP}$$
   * *Recall* ($R$): Mengukur kemampuan model dalam mendeteksi seluruh kendaraan yang sebenarnya ada pada data rujukan (*ground truth*):
     $$R = \frac{TP}{TP + FN}$$
   * *Mean Average Precision* (mAP): Rata-rata dari nilai *Average Precision* (AP) pada seluruh kelas kendaraan untuk ambang batas IoU tertentu (misal mAP@0.5).
3. **Evaluasi Akurasi Penghitungan Kendaraan (*Counting Accuracy*):**
   Akurasi penghitungan kendaraan diukur dengan membandingkan total kendaraan yang dihitung oleh sistem otomatis ($C_{sys}$) terhadap hasil penghitungan manual manusia ($C_{man}$) sebagai data kebenaran (*ground truth*):
   $$\text{Akurasi} = \left( 1 - \frac{|C_{sys} - C_{man}|}{C_{man}} \right) \times 100\%$$

---

## 2.3 Kerangka Pemikiran

Kerangka pemikiran menggambarkan alur logis pemecahan masalah penelitian secara sistematis, yang merangkum korelasi antara identifikasi masalah, tujuan yang ingin dicapai, tahapan eksperimen (data, metode, dan alat bantu yang digunakan), skenario pengujian, hingga keluaran (*output*) hasil penelitian. Struktur kerangka pemikiran disajikan pada Tabel 2.6.

**Tabel 2.6 Kerangka Pemikiran**

| Komponen | Uraian |
| :--- | :--- |
| **Masalah** | 1. Pemantauan lalu lintas pada lima gerbang tol di Kota Semarang (Kaligawe, Krapyak, Jatingaleh, Banyumanik, Tembalang) yang mengandalkan CCTV konvensional masih sebatas visual tanpa data jumlah dan klasifikasi kendaraan otomatis.<br>2. Pengamatan dan penghitungan kendaraan secara manual membutuhkan biaya dan sumber daya besar serta tidak dapat menyajikan data statistik secara kontinu dan *real-time*.<br>3. Deteksi objek berbasis frame tunggal tanpa pelacak memicu risiko penghitungan ganda (*double counting*), terutama saat kondisi lalu lintas padat yang mengalami oklusi (*occlusion*).<br>4. Belum tersedianya sistem terintegrasi yang menggabungkan kecerdasan buatan, basis data spasial, dan WebGIS untuk memantau lima gerbang tol sekaligus dalam satu portal. |
| **Tujuan** | 1. Merancang dan mengimplementasikan sistem deteksi, klasifikasi, pelacakan, dan penghitungan kendaraan otomatis berbasis YOLOv11 dan ByteTrack pada citra CCTV gerbang tol Kota Semarang.<br>2. Mengembangkan portal WebGIS terintegrasi berbasis PostgreSQL/PostGIS dan Laravel untuk memvisualisasikan peta lokasi gerbang tol, pemantauan multi-kamera, indikator status kepadatan, dan statistik berkala (harian, mingguan, bulanan).<br>3. Mengevaluasi performa sistem dalam mendeteksi dan menghitung kendaraan serta memvalidasi fungsionalitas sistem melalui pengujian *Black Box*. |
| **Eksperimen** | **Data:**<br>Aliran siaran video CCTV pada lima titik gerbang tol Kota Semarang (Kaligawe, Krapyak, Jatingaleh, Banyumanik, Tembalang) dari platform PantauSemar (Diskomdigi Kota Semarang/Provinsi Jawa Tengah) serta rekaman video lokal sebagai *fallback* data.<br><br>**Metode:**<br>1. *Metode Pengembangan Sistem:* Waterfall (Analisis Kebutuhan, Desain Sistem, Implementasi, Pengujian, Pemeliharaan).<br>2. *Metode Deteksi & Klasifikasi:* YOLOv11 untuk 4 kelas kendaraan (mobil, sepeda motor, bus, truk).<br>3. *Metode Pelacakan:* ByteTrack (asosiasi deteksi kotak skor tinggi dan rendah dengan Kalman Filter).<br>4. *Metode Penghitungan:* Polygon Crossing Area (*Point-in-Polygon*).<br>5. *Metode Spasial & WebGIS:* PostgreSQL/PostGIS (geometri titik WGS 84 EPSG:4326) dan Leaflet.js.<br><br>**Alat Bantu (Tools):**<br>*Perangkat Lunak (Software):*<br>- Bahasa Pemrograman: Python 3.13, PHP 8.2, JavaScript<br>- Framework: FastAPI (AI Engine), Laravel 11 (WebGIS Portal)<br>- Basis Data: PostgreSQL 16 dengan ekstensi PostGIS 3.4<br>- Pustaka: OpenCV, Supervision, Ultralytics YOLO, Leaflet.js, Chart.js<br>- Editor & Server: Visual Studio Code, Laragon, Git<br>*Perangkat Keras (Hardware):*<br>- Laptop/PC dengan GPU NVIDIA GeForce RTX, RAM 16 GB, Prosesor Multi-Core. |
| **Pengujian** | 1. *Black Box Testing:* Menguji fungsi antarmuka WebGIS, pemutaran siaran langsung, filter gerbang tol, dan peralihan tab statistik periode.<br>2. *Pengujian Model Computer Vision:* Mengukur metrik *Precision*, *Recall*, dan *Mean Average Precision* (mAP).<br>3. *Pengujian Akurasi Penghitungan:* Mengukur persentase akurasi penghitungan kendaraan otomatis terhadap hasil pencatatan manual (*ground truth*). |
| **Hasil** | Sistem pemantauan lalu lintas cerdas terintegrasi **Semarang Toll Eye** berbasis *Computer Vision* dan WebGIS yang mampu mendeteksi, mengklasifikasikan, melacak, dan menghitung volume kendaraan secara otomatis pada lima gerbang tol di Kota Semarang, serta menyajikan visualisasi peta interaktif, indikator kondisi lalu lintas, dan analisis statistik volume kendaraan berbasis periode waktu. |

Konsep yang diusulkan pada tugas akhir ini berakar dari kebutuhan mendesak untuk mentransformasikan data visual kamera CCTV pemantauan kota di lingkungan Dinas Komunikasi dan Informatika Digital (Diskomdigi) Provinsi Jawa Tengah menjadi informasi analitik yang terstruktur dan berdaya guna tinggi. Melalui kerangka pemikiran yang dirumuskan pada Tabel 2.6, sistem **Semarang Toll Eye** memadukan keunggulan komputasi deteksi objek terkini **YOLOv11** dengan keandalan pelacakan multi-objek **ByteTrack** guna mengatasi fenomena oklusi dan penghitungan ganda pada lalu lintas padat. Data perlintasan kendaraan yang melintasi zona batas poligon virtual selanjutnya diproses secara *asynchronous* oleh *microservice* FastAPI dan ditransmisikan ke basis data spasial **PostgreSQL/PostGIS** untuk agregasi statistik periodik (harian, mingguan, dan bulanan). Seluruh informasi kuantitatif, status kepadatan lalu lintas (*lancar*, *padat*, dan *macet*), serta *live streaming* kamera CCTV disajikan secara terpusat melalui portal **WebGIS** berbasis Laravel dan Leaflet.js. Dengan alur terintegrasi ini, sistem tidak hanya mempermudah pengelola dan pengguna dalam mengamati kondisi lalu lintas lima gerbang tol secara simultan, namun juga menyediakan basis data historis yang valid untuk mendukung evaluasi dan perencanaan kebijakan transportasi berbasis data (*data-driven decision making*).
