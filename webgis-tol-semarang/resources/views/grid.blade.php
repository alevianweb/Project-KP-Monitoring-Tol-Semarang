@extends('layouts.app')

@section('title', 'Command Center - Grid CCTV')

@section('styles')
<style>
    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .grid-card {
        background: var(--card-bg);
        backdrop-filter: blur(16px);
        border: 2px solid var(--card-border);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
    }

    /* The glowing border animation for the busiest camera */
    .grid-card.is-busiest {
        border-color: var(--accent-red);
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.5), inset 0 0 10px rgba(239, 68, 68, 0.3);
        animation: card-pulse-red 2s infinite;
    }

    @keyframes card-pulse-red {
        0% { box-shadow: 0 0 15px rgba(239, 68, 68, 0.4), inset 0 0 10px rgba(239, 68, 68, 0.2); }
        50% { box-shadow: 0 0 30px rgba(239, 68, 68, 0.8), inset 0 0 15px rgba(239, 68, 68, 0.4); }
        100% { box-shadow: 0 0 15px rgba(239, 68, 68, 0.4), inset 0 0 10px rgba(239, 68, 68, 0.2); }
    }

    .grid-header {
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(0, 0, 0, 0.2);
        border-bottom: 1px solid var(--card-border);
    }

    .grid-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .grid-video-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 16/9;
        background: #000;
    }

    .grid-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .grid-footer {
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        color: var(--text-secondary);
        background: rgba(0, 0, 0, 0.2);
        border-top: 1px solid var(--card-border);
    }

    .status-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .status-lancar { background: rgba(16, 185, 129, 0.15); color: var(--accent-green, #10b981); border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-padat { background: rgba(245, 158, 11, 0.15); color: var(--accent-yellow, #f59e0b); border: 1px solid rgba(245, 158, 11, 0.2); }
    .status-macet { background: rgba(239, 68, 68, 0.15); color: var(--accent-red, #ef4444); border: 1px solid rgba(239, 68, 68, 0.2); }
    .status-offline { background: rgba(156, 163, 175, 0.15); color: var(--text-secondary, #9ca3af); border: 1px solid rgba(156, 163, 175, 0.2); }

    .pulse-dot-green { width: 6px; height: 6px; border-radius: 50%; display: inline-block; background-color: var(--accent-green, #10b981); animation: pulse-green 1.6s infinite; }
    .pulse-dot-yellow { width: 6px; height: 6px; border-radius: 50%; display: inline-block; background-color: var(--accent-yellow, #f59e0b); animation: pulse-yellow 1.2s infinite; }
    .pulse-dot-red { width: 6px; height: 6px; border-radius: 50%; display: inline-block; background-color: var(--accent-red, #ef4444); animation: pulse-red 0.8s infinite; }
    .pulse-dot-offline { width: 6px; height: 6px; border-radius: 50%; display: inline-block; background-color: var(--text-secondary, #9ca3af); }

    @keyframes pulse-green { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
    @keyframes pulse-yellow { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); } }
    @keyframes pulse-red { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8); } 70% { transform: scale(1); box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    .page-header {
        margin-bottom: 2rem;
        text-align: center;
    }
    .page-header h1 {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #3b82f6, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }
    .page-header p {
        color: var(--text-secondary);
        font-size: 1rem;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>Command Center</h1>
    <p>Live Monitoring Seluruh Gerbang Tol Semarang</p>
</div>

<div class="grid-container">
    @foreach($cameras as $camera)
    <div class="grid-card" id="cam-card-{{ $camera->id }}">
        <div class="grid-header">
            <div class="grid-title">
                <i class="fa-solid fa-video" style="color: #3b82f6;"></i>
                {{ $camera->name }}
            </div>
            <span class="status-badge status-offline" id="grid-status-{{ $camera->id }}">
                <span class="pulse-dot-offline"></span> Offline
            </span>
        </div>
        <div class="grid-video-wrapper">
            <img class="grid-video" src="http://localhost:8001/stream/{{ $camera->id }}" alt="CCTV Stream {{ $camera->name }}" 
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=600&auto=format&fit=crop'; console.log('Stream offline for {{ $camera->name }}');">
        </div>
        <div class="grid-footer">
            <span><i class="fa-solid fa-location-dot"></i> {{ $camera->location }}</span>
            <span id="vehicle-count-{{ $camera->id }}" style="font-weight: 600; color: var(--text-primary);">0 Kendaraan</span>
        </div>
    </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script>
    const camerasData = @json($cameras);

    function checkCameraStatus() {
        fetch('http://localhost:8001/status')
            .then(response => response.json())
            .then(data => {
                let maxVehicles = -1;
                let busiestCamId = null;

                // First pass to find busiest
                camerasData.forEach(cam => {
                    const statusData = data[cam.id];
                    if (statusData && statusData.status === 'online') {
                        if (statusData.vehicles > maxVehicles) {
                            maxVehicles = statusData.vehicles;
                            busiestCamId = cam.id;
                        }
                    }
                });

                // Threshold for "busiest" warning glow (at least 16 vehicles)
                if (maxVehicles < 16) {
                    busiestCamId = null;
                }

                camerasData.forEach(cam => {
                    const statusData = data[cam.id] || 'offline';
                    updateGridUI(cam.id, statusData, cam.id === busiestCamId);
                });
            })
            .catch(error => {
                camerasData.forEach(cam => {
                    updateGridUI(cam.id, 'offline', false);
                });
            });
    }

    function updateGridUI(camId, statusData, isBusiest) {
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
        
        const badge = document.getElementById(`grid-status-${camId}`);
        if (badge) {
            badge.className = `status-badge ${badgeClass}`;
            badge.innerHTML = badgeHtml;
        }

        const countEl = document.getElementById(`vehicle-count-${camId}`);
        if (countEl) {
            countEl.innerText = `${vehiclesCount} Kendaraan`;
            if (statusString === 'offline') countEl.innerText = '0 Kendaraan';
        }

        const card = document.getElementById(`cam-card-${camId}`);
        if (card) {
            if (isBusiest) {
                card.classList.add('is-busiest');
            } else {
                card.classList.remove('is-busiest');
            }
        }
    }

    setInterval(checkCameraStatus, 3000); // Check every 3 seconds for grid
    setTimeout(checkCameraStatus, 500); // initial
</script>
@endsection
