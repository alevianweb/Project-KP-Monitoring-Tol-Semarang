<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Monitoring Kendaraan Tol Semarang</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/jateng.png') }}">
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Leaflet GIS CSS (For WebGIS Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <script>
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>

    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(17, 24, 39, 0.7);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.35);
            --accent-green: #10b981;
            --accent-yellow: #f59e0b;
            --accent-red: #ef4444;
            --font-family: 'Outfit', sans-serif;
            
            --nav-bg: rgba(11, 15, 25, 0.8);
            --footer-bg: rgba(11, 15, 25, 0.5);
            --item-bg: rgba(255, 255, 255, 0.02);
            --item-hover-bg: rgba(59, 130, 246, 0.05);
            --item-border: rgba(255, 255, 255, 0.04);
            --item-active-bg: rgba(59, 130, 246, 0.1);
            --map-bg: #0f172a;
            --popup-bg: #111827;
            --popup-text: #f3f4f6;
            --popup-border: rgba(255, 255, 255, 0.1);
            --input-bg: #111827;
            --chart-grid: rgba(255, 255, 255, 0.08);
        }

        [data-theme="light"] {
            --bg-color: #f1f5f9;
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: rgba(0, 0, 0, 0.08);
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --accent-glow: rgba(59, 130, 246, 0.15);
            
            --nav-bg: rgba(248, 250, 252, 0.85);
            --footer-bg: rgba(241, 245, 249, 0.8);
            --item-bg: rgba(0, 0, 0, 0.03);
            --item-hover-bg: rgba(59, 130, 246, 0.08);
            --item-border: rgba(0, 0, 0, 0.04);
            --item-active-bg: rgba(59, 130, 246, 0.12);
            --map-bg: #e2e8f0;
            --popup-bg: #ffffff;
            --popup-text: #1e293b;
            --popup-border: rgba(0, 0, 0, 0.1);
            --input-bg: #ffffff;
            --chart-grid: rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 10% 20%, rgba(59, 130, 246, 0.1) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(16, 185, 129, 0.07) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* App Navbar Container */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 2.5rem;
            background: var(--nav-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--card-border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: var(--text-primary);
            text-decoration: none;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar-brand i {
            -webkit-text-fill-color: initial;
            color: #3b82f6;
        }

        .navbar-menu {
            display: flex;
            gap: 1.5rem;
        }

        .navbar-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-link:hover, .navbar-link.active {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: inset 0 0 0 1px var(--card-border);
        }

        .navbar-link.active {
            color: var(--accent);
            box-shadow: inset 0 0 0 1px var(--accent-glow);
            background: rgba(59, 130, 246, 0.1);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Glassmorphism Cards */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 12px 40px -10px rgba(0, 0, 0, 0.5);
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.75rem;
        }

        /* Grid Utilities */
        .grid {
            display: grid;
            gap: 1.5rem;
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        footer {
            text-align: center;
            padding: 2.5rem;
            color: var(--text-secondary);
            font-size: 0.85rem;
            border-top: 1px solid var(--card-border);
            margin-top: 3rem;
            background: var(--footer-bg);
        }

        /* Leaflet popup customization */
        .leaflet-popup-content-wrapper, .leaflet-popup-tip {
            background-color: var(--popup-bg) !important;
            color: var(--popup-text) !important;
            border: 1px solid var(--popup-border) !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
        }
        .leaflet-popup-close-button {
            color: var(--text-secondary) !important;
        }
    </style>
    @yield('styles')
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand" style="display: flex; align-items: center;">
            <img src="{{ asset('images/udinus.png') }}" alt="Logo UDINUS" style="height: 45px; width: auto; object-fit: contain;">
            <span style="color: rgba(255, 255, 255, 0.2); margin: 0 0.75rem; font-size: 1.5rem; font-weight: 300;">|</span>
            <img src="{{ asset('images/jateng.png') }}" alt="Logo Jateng" style="height: 45px; width: auto; object-fit: contain; margin-right: 0.75rem;">
            <span>SEMARANG TOLL EYE</span>
        </a>
        <div class="navbar-menu">
            <a href="{{ route('dashboard') }}" class="navbar-link {{ Route::is('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-map-location-dot"></i> WebGIS CCTV
            </a>
            <a href="{{ route('grid') }}" class="navbar-link {{ Route::is('grid') ? 'active' : '' }}">
                <i class="fa-solid fa-desktop"></i> Command Center
            </a>
            <a href="{{ route('analytics') }}" class="navbar-link {{ Route::is('analytics') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Dashboard Analisis
            </a>
            <button id="theme-toggle" class="navbar-link" style="border: none; background: none; cursor: pointer;" title="Ganti Tema">
                <i class="fa-solid fa-moon" id="theme-icon"></i> Mode
            </button>
        </div>
    </nav>

    <div class="container animate-fade-in">
        @yield('content')
    </div>

    <footer>
        <p>&copy; 2026 Semarang Toll Eye - Monitoring Gerbang Tol Semarang. Project Kerja Praktek.</p>
    </footer>

    <!-- Leaflet GIS JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        
        if (localStorage.getItem('theme') === 'light') {
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }
        
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (currentTheme === 'light') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
            
            window.dispatchEvent(new Event('themeChanged'));
        });
    </script>

    @yield('scripts')
</body>
</html>
