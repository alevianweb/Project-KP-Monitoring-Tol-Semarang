@extends('layouts.app')

@section('title', 'WebGIS Pemantauan CCTV')

@section('styles')
<style>
    .map-container {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 1.5rem;
        height: calc(100vh - 160px);
        min-height: 550px;
    }

    /* Sidebar list of cameras */
    .camera-list-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .camera-items {
        overflow-y: auto;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding-right: 0.25rem;
    }

    .camera-item {
        background: var(--item-bg);
        border: 1px solid var(--item-border);
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .camera-item:hover {
        background: var(--item-hover-bg);
        border-color: rgba(59, 130, 246, 0.2);
        transform: translateY(-2px);
    }

    .camera-item.active {
        background: var(--item-active-bg);
        border-color: var(--accent);
    }

    .camera-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .camera-name {
        font-weight: 600;
        font-size: 1rem;
        color: var(--text-primary);
    }

    .status-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .status-lancar {
        background: rgba(16, 185, 129, 0.15);
        color: var(--accent-green, #10b981);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .status-padat {
        background: rgba(245, 158, 11, 0.15);
        color: var(--accent-yellow, #f59e0b);
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .status-macet {
        background: rgba(239, 68, 68, 0.15);
        color: var(--accent-red, #ef4444);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .status-offline {
        background: rgba(156, 163, 175, 0.15);
        color: var(--text-secondary, #9ca3af);
        border: 1px solid rgba(156, 163, 175, 0.2);
    }

    .camera-loc {
        font-size: 0.8rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    /* Map view wrapper */
    .map-card {
        padding: 0;
        overflow: hidden;
        position: relative;
        height: 100%;
    }

    #map {
        width: 100%;
        height: 100%;
        z-index: 10;
        background: var(--map-bg);
    }

    /* Live Popup Styling */
    .popup-content {
        width: 280px;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        font-family: var(--font-family);
    }

    .popup-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--popup-text);
        margin-bottom: 2px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .popup-video-container {
        width: 100%;
        position: relative;
        background: #000;
        border-radius: 8px;
        overflow: hidden;
        aspect-ratio: 16/9;
        border: 1px solid var(--popup-border);
    }

    .popup-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .popup-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 4px;
        gap: 0.5rem;
    }

    .btn {
        flex: 1;
        padding: 0.4rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 0.35rem;
        border: none;
    }

    .btn-primary {
        background: #3b82f6;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #2563eb;
        color: #ffffff !important;
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.08);
        color: #d1d5db;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    /* Custom scrollbar for camera list */
    .camera-items::-webkit-scrollbar {
        width: 6px;
    }
    .camera-items::-webkit-scrollbar-track {
        background: transparent;
    }
    .camera-items::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 9999px;
    }
    .camera-items::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Dynamic pulsing dots */
    .pulse-dot-green {
        width: 6px; height: 6px; border-radius: 50%; display: inline-block;
        background-color: var(--accent-green, #10b981);
        animation: pulse-green 1.6s infinite;
    }
    .pulse-dot-yellow {
        width: 6px; height: 6px; border-radius: 50%; display: inline-block;
        background-color: var(--accent-yellow, #f59e0b);
        animation: pulse-yellow 1.2s infinite;
    }
    .pulse-dot-red {
        width: 6px; height: 6px; border-radius: 50%; display: inline-block;
        background-color: var(--accent-red, #ef4444);
        animation: pulse-red 0.8s infinite;
    }
    .pulse-dot-offline {
        width: 6px; height: 6px; border-radius: 50%; display: inline-block;
        background-color: var(--text-secondary, #9ca3af);
    }

    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    @keyframes pulse-yellow {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }
    @keyframes pulse-red {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8); }
        70% { transform: scale(1); box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    /* Zoom Button & Modal Styles */
    .zoom-btn {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        border-radius: 4px;
        padding: 6px 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        z-index: 10;
    }
    .zoom-btn:hover {
        background: rgba(0, 0, 0, 0.8);
    }
    .zoom-btn i {
        font-size: 14px;
    }

    .zoom-modal {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .zoom-modal-title {
        color: white;
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .zoomed-video {
        max-width: 90%;
        max-height: 80%;
        object-fit: contain;
        border: 2px solid var(--card-border);
        border-radius: 8px;
    }

    .close-zoom {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
    }

    .close-zoom:hover {
        color: #bbb;
    }
</style>
@section('content')
<div class="map-container">
    
    <!-- Sidebar -->
    <div class="card camera-list-card">
        <div class="card-title">
            <i class="fa-solid fa-video"></i>
            <span>Daftar Kamera CCTV</span>
        </div>
        <div class="camera-items">
            @foreach($cameras as $camera)
                <div class="camera-item" id="cam-item-{{ $camera->id }}" onclick="focusCamera({{ $camera->id }}, {{ $camera->latitude }}, {{ $camera->longitude }})">
                    <div class="camera-item-header">
                        <span class="camera-name">{{ $camera->name }}</span>
                        <span class="status-badge status-offline" id="cam-status-{{ $camera->id }}">
                            <span class="pulse-dot-offline"></span> Offline
                        </span>
                    </div>
                    <span class="camera-loc">
                        <i class="fa-solid fa-location-dot"></i>
                        {{ $camera->location }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Map View -->
    <div class="card map-card">
        <div id="map"></div>
    </div>

</div>

<!-- Zoom Modal -->
<div id="zoomModal" class="zoom-modal">
    <span class="close-zoom" onclick="closeZoomModal()">&times;</span>
    <h3 id="zoomModalTitle" class="zoom-modal-title"></h3>
    <img id="zoomedVideo" class="zoomed-video" src="" alt="Zoomed Video">
</div>
@endsection

@section('scripts')
<script>
    // Initialize Leaflet Map centered on Semarang
    const map = L.map('map', {
        zoomControl: false // custom position below
    }).setView([-7.02, 110.42], 12);

    // Determine initial theme for tiles
    const initialTheme = localStorage.getItem('theme') === 'light' ? 'light' : 'dark';
    const tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

    // Premium Tile Layer
    let tileLayer = L.tileLayer(tileUrl, {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Function to apply dark mode filter to map tiles
    function applyMapTheme(isLight) {
        const tilePane = document.querySelector('.leaflet-tile-pane');
        if (tilePane) {
            if (isLight) {
                tilePane.style.filter = 'none';
            } else {
                tilePane.style.filter = 'brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7)';
            }
        }
    }

    // Apply initial theme
    setTimeout(() => applyMapTheme(initialTheme === 'light'), 100);

    // Listen to theme changes to swap tiles
    window.addEventListener('themeChanged', () => {
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        applyMapTheme(isLight);
    });

    // Add zoom control at bottom right
    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    // Store markers for focusing
    const markers = {};

    // Camera icons
    const cctvIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `
            <div style="
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                color: white;
                width: 38px;
                height: 38px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid white;
                box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
                cursor: pointer;
            ">
                <i class="fa-solid fa-video" style="font-size: 14px;"></i>
            </div>
        `,
        iconSize: [38, 38],
        iconAnchor: [19, 19],
        popupAnchor: [0, -19]
    });

    // Populate cameras
    const camerasData = @json($cameras);
    
    camerasData.forEach(cam => {
        const marker = L.marker([cam.latitude, cam.longitude], { icon: cctvIcon }).addTo(map);
        
        // Popup template with live MJPEG streaming pointing to FastAPI (running on port 8000)
        const popupContent = `
            <div class="popup-content">
                <div class="popup-title">
                    <span>${cam.name}</span>
                    <span class="status-badge status-offline" id="popup-status-${cam.id}"><span class="pulse-dot-offline"></span> Offline</span>
                </div>
                <div class="popup-loc" style="font-size: 0.75rem; color: #9ca3af; margin-bottom: 4px;">
                    <i class="fa-solid fa-location-dot"></i> ${cam.location}
                </div>
                <div class="popup-video-container">
                    <!-- Point to the FastAPI backend stream -->
                    <img class="popup-video" src="http://localhost:8001/stream/${cam.id}" alt="CCTV Stream ${cam.name}" 
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=600&auto=format&fit=crop'; console.log('FastAPI stream offline. Showing fallback image.');">
                    <button class="zoom-btn" onclick="openZoomModal('http://localhost:8001/stream/${cam.id}', '${cam.name}')" title="Perbesar Video">
                        <i class="fa-solid fa-expand"></i>
                    </button>
                </div>
                <div class="popup-actions">
                    <a href="/analytics?camera_id=${cam.id}" class="btn btn-primary">
                        <i class="fa-solid fa-chart-bar"></i> Analisis Tren
                    </a>
                    <button class="btn btn-secondary" onclick="map.closePopup();">
                        Tutup
                    </button>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        markers[cam.id] = marker;

        // Custom action when opening/closing popup to handle list active states
        marker.on('popupopen', function() {
            document.querySelectorAll('.camera-item').forEach(item => item.classList.remove('active'));
            const listItem = document.getElementById(`cam-item-${cam.id}`);
            if (listItem) {
                listItem.classList.add('active');
                listItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });

    // Function to pan and focus a camera from sidebar click
    function focusCamera(id, lat, lng) {
        document.querySelectorAll('.camera-item').forEach(item => item.classList.remove('active'));
        document.getElementById(`cam-item-${id}`).classList.add('active');
        
        map.flyTo([lat, lng], 14, {
            duration: 1.2
        });
        
        setTimeout(() => {
            markers[id].openPopup();
        }, 1200);
    }

    // Zoom modal functions
    function openZoomModal(src, name) {
        document.getElementById('zoomModal').style.display = 'flex';
        document.getElementById('zoomedVideo').src = src;
        document.getElementById('zoomModalTitle').innerText = name;
    }

    function closeZoomModal() {
        document.getElementById('zoomModal').style.display = 'none';
        document.getElementById('zoomedVideo').src = '';
    }

    // Function to check camera status
    function checkCameraStatus() {
        fetch('http://localhost:8001/status')
            .then(response => response.json())
            .then(data => {
                camerasData.forEach(cam => {
                    const status = data[cam.id] || 'offline';
                    updateCameraUI(cam.id, status);
                });
            })
            .catch(error => {
                // If API is down, all offline
                camerasData.forEach(cam => {
                    updateCameraUI(cam.id, 'offline');
                });
            });
    }

    function updateCameraUI(camId, statusData) {
        let badgeClass = 'status-offline';
        let badgeHtml = '<span class="pulse-dot-offline"></span> Offline';
        
        let statusString = (typeof statusData === 'object') ? statusData.status : statusData;
        let vehiclesCount = (typeof statusData === 'object') ? statusData.vehicles : 0;

        if (statusString === 'online') {
            if (vehiclesCount <= 12) {
                badgeClass = 'status-lancar';
                badgeHtml = '<span class="pulse-dot-green"></span> Lancar';
            } else if (vehiclesCount <= 25) {
                badgeClass = 'status-padat';
                badgeHtml = '<span class="pulse-dot-yellow"></span> Padat';
            } else {
                badgeClass = 'status-macet';
                badgeHtml = '<span class="pulse-dot-red"></span> Macet';
            }
        }
        
        const badge = document.getElementById(`cam-status-${camId}`);
        if (badge) {
            badge.className = `status-badge ${badgeClass}`;
            badge.innerHTML = badgeHtml;
        }
        
        const popupBadge = document.getElementById(`popup-status-${camId}`);
        if (popupBadge) {
            popupBadge.className = `status-badge ${badgeClass}`;
            popupBadge.innerHTML = badgeHtml;
        }
    }

    // Check status periodically
    setInterval(checkCameraStatus, 5000);
    setTimeout(checkCameraStatus, 1000); // initial check
</script>
@endsection
