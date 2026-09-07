@extends('layouts.app')

@section('title', 'Analisis Statistik Kendaraan')

@section('styles')
<style>
    .filter-section {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 800;
        background: linear-gradient(135deg, #fff, #9ca3af);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    [data-theme="light"] .page-header h1 {
        background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .form-select {
        background-color: var(--input-bg);
        color: var(--text-primary);
        border: 1px solid var(--card-border);
        border-radius: 10px;
        padding: 0.6rem 2.5rem 0.6rem 1.25rem;
        font-size: 0.95rem;
        font-family: var(--font-family);
        font-weight: 500;
        cursor: pointer;
        outline: none;
        transition: border-color 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.25rem auto;
    }

    .form-select:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 2px var(--accent-glow);
    }

    /* Cards grid */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        z-index: 2;
    }

    /* Shine effect */
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 50%;
        height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-20deg);
        transition: left 0.6s ease;
    }
    
    [data-theme="light"] .stat-card::after {
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
    }

    .stat-card:hover::after {
        left: 150%;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        top: -75px;
        right: -75px;
        opacity: 0.03;
    }

    .stat-card-total::before { background-color: var(--accent); }
    .stat-card-car::before { background-color: var(--accent-green); }
    .stat-card-moto::before { background-color: var(--accent-yellow); }
    .stat-card-bus::before { background-color: #8b5cf6; }
    .stat-card-truck::before { background-color: var(--accent-red); }

    .stat-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    .icon-total { background: rgba(59, 130, 246, 0.1); color: var(--accent); }
    .icon-car { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
    .icon-moto { background: rgba(245, 158, 11, 0.1); color: var(--accent-yellow); }
    .icon-bus { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
    .icon-truck { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); }

    /* Auto Trends Section */
    .trends-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .trend-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .trend-period {
        font-size: 0.8rem;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
    }

    .trend-comparison {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .trend-figures {
        display: flex;
        flex-direction: column;
    }

    .trend-current {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .trend-prev {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .trend-percentage {
        font-size: 0.95rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .percentage-up {
        background: rgba(16, 185, 129, 0.12);
        color: var(--accent-green);
    }

    .percentage-down {
        background: rgba(239, 68, 68, 0.12);
        color: var(--accent-red);
    }

    .percentage-neutral {
        background: rgba(156, 163, 175, 0.12);
        color: var(--text-secondary);
    }

    /* Chart section */
    .chart-container-2 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 900px) {
        .chart-container-2 {
            grid-template-columns: 1fr;
        }
    }

    .chart-box {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>
@endsection

@section('content')
<div class="filter-section">
    <div class="page-header">
        <h1>Dashboard Analisa Kendaraan</h1>
        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.35rem;">
            Waktu Sekarang: <span class="live-clock">{{ \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s') }}</span> WIB | Terakhir diupdate: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB
        </p>
    </div>
    
    <!-- Filter form -->
    <form action="{{ route('analytics') }}" method="GET" id="filter-form">
        <select name="camera_id" class="form-select" onchange="document.getElementById('filter-form').submit();">
            <option value="">Semua Gerbang Tol (Agregat)</option>
            @foreach($cameras as $camera)
                <option value="{{ $camera->id }}" {{ $cameraId == $camera->id ? 'selected' : '' }}>
                    Gerbang Tol {{ $camera->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<!-- 1.1 SUMMARY STATS CARDS (HARI INI) -->
<div style="margin-bottom: 1rem;">
    <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--text-primary);">
        <i class="fa-solid fa-calendar-day" style="color: var(--accent); margin-right: 0.5rem;"></i>
        Total Volume Kendaraan Hari Ini (kend./hari)
    </h3>
    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem; margin-left: 1.75rem;">
        Diupdate: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB
    </div>
</div>
<div class="summary-grid">
    <div class="stat-card stat-card-total">
        <div class="stat-info">
            <span class="stat-label">Total Kendaraan</span>
            <span class="stat-value count-up" data-target="{{ $todayTotal }}">0</span>
        </div>
        <div class="stat-icon icon-total">
            <i class="fa-solid fa-gauge-high"></i>
        </div>
    </div>
    <div class="stat-card stat-card-car">
        <div class="stat-info">
            <span class="stat-label">Mobil Pribadi</span>
            <span class="stat-value count-up" data-target="{{ $todayCarCount }}">0</span>
        </div>
        <div class="stat-icon icon-car">
            <i class="fa-solid fa-car"></i>
        </div>
    </div>
    <div class="stat-card stat-card-moto">
        <div class="stat-info">
            <span class="stat-label">Sepeda Motor</span>
            <span class="stat-value count-up" data-target="{{ $todayMotorcycleCount }}">0</span>
        </div>
        <div class="stat-icon icon-moto">
            <i class="fa-solid fa-motorcycle"></i>
        </div>
    </div>
    <div class="stat-card stat-card-bus">
        <div class="stat-info">
            <span class="stat-label">Bus</span>
            <span class="stat-value count-up" data-target="{{ $todayBusCount }}">0</span>
        </div>
        <div class="stat-icon icon-bus">
            <i class="fa-solid fa-bus"></i>
        </div>
    </div>
    <div class="stat-card stat-card-truck">
        <div class="stat-info">
            <span class="stat-label">Truk</span>
            <span class="stat-value count-up" data-target="{{ $todayTruckCount }}">0</span>
        </div>
        <div class="stat-icon icon-truck">
            <i class="fa-solid fa-truck"></i>
        </div>
    </div>
</div>

<!-- 1.2 SUMMARY STATS CARDS (BULAN INI) -->
<div style="margin-bottom: 1rem;">
    <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--text-primary);">
        <i class="fa-solid fa-calendar-check" style="color: var(--accent); margin-right: 0.5rem;"></i>
        Total Volume Kendaraan Bulan Ini (kend./bulan)
    </h3>
    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem; margin-left: 1.75rem;">
        Diupdate: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB
    </div>
</div>
<div class="summary-grid">
    <div class="stat-card stat-card-total">
        <div class="stat-info">
            <span class="stat-label">Total Kendaraan</span>
            <span class="stat-value count-up" data-target="{{ $totalCount }}">0</span>
        </div>
        <div class="stat-icon icon-total">
            <i class="fa-solid fa-gauge-high"></i>
        </div>
    </div>
    <div class="stat-card stat-card-car">
        <div class="stat-info">
            <span class="stat-label">Mobil Pribadi</span>
            <span class="stat-value count-up" data-target="{{ $carCount }}">0</span>
        </div>
        <div class="stat-icon icon-car">
            <i class="fa-solid fa-car"></i>
        </div>
    </div>
    <div class="stat-card stat-card-moto">
        <div class="stat-info">
            <span class="stat-label">Sepeda Motor</span>
            <span class="stat-value count-up" data-target="{{ $motorcycleCount }}">0</span>
        </div>
        <div class="stat-icon icon-moto">
            <i class="fa-solid fa-motorcycle"></i>
        </div>
    </div>
    <div class="stat-card stat-card-bus">
        <div class="stat-info">
            <span class="stat-label">Bus</span>
            <span class="stat-value count-up" data-target="{{ $busCount }}">0</span>
        </div>
        <div class="stat-icon icon-bus">
            <i class="fa-solid fa-bus"></i>
        </div>
    </div>
    <div class="stat-card stat-card-truck">
        <div class="stat-info">
            <span class="stat-label">Truk</span>
            <span class="stat-value count-up" data-target="{{ $truckCount }}">0</span>
        </div>
        <div class="stat-icon icon-truck">
            <i class="fa-solid fa-truck"></i>
        </div>
    </div>
</div>

<!-- 2. AUTOMATIC STATISTICAL TRENDS -->
<div style="margin-bottom: 1rem;">
    <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--text-primary);">
        <i class="fa-solid fa-chart-line" style="color: var(--accent); margin-right: 0.5rem;"></i>
        Otomatis Statistik Tren Kendaraan (Traffic Growth)
    </h3>
    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-left: 1.75rem;">
        Diupdate: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB
    </div>
</div>
<div class="trends-grid">
    <!-- Today vs Yesterday -->
    <div class="trend-card">
        <span class="trend-period">Harian (Hari Ini vs Kemarin)</span>
        <div class="trend-comparison">
            <div class="trend-figures">
                <span class="trend-current"><span class="count-up" data-target="{{ $todayTotal }}">0</span> <span style="font-size:0.75rem; font-weight:normal; color:var(--text-secondary);">kendaraan</span></span>
                <span class="trend-prev">Kemarin: <span class="count-up" data-target="{{ $yesterdayTotal }}">0</span></span>
            </div>
            
            @if($dailyGrowth > 0)
                <span class="trend-percentage percentage-up">
                    <i class="fa-solid fa-arrow-trend-up"></i> +{{ number_format($dailyGrowth, 1) }}%
                </span>
            @elseif($dailyGrowth < 0)
                <span class="trend-percentage percentage-down">
                    <i class="fa-solid fa-arrow-trend-down"></i> {{ number_format($dailyGrowth, 1) }}%
                </span>
            @else
                <span class="trend-percentage percentage-neutral">
                    <i class="fa-solid fa-minus"></i> 0%
                </span>
            @endif
        </div>
    </div>

    <!-- This Week vs Last Week -->
    <div class="trend-card">
        <span class="trend-period">Mingguan (Minggu Ini vs Minggu Lalu)</span>
        <div class="trend-comparison">
            <div class="trend-figures">
                <span class="trend-current"><span class="count-up" data-target="{{ $thisWeekTotal }}">0</span> <span style="font-size:0.75rem; font-weight:normal; color:var(--text-secondary);">kendaraan</span></span>
                <span class="trend-prev">Minggu Lalu: <span class="count-up" data-target="{{ $lastWeekTotal }}">0</span></span>
            </div>
            
            @if($weeklyGrowth > 0)
                <span class="trend-percentage percentage-up">
                    <i class="fa-solid fa-arrow-trend-up"></i> +{{ number_format($weeklyGrowth, 1) }}%
                </span>
            @elseif($weeklyGrowth < 0)
                <span class="trend-percentage percentage-down">
                    <i class="fa-solid fa-arrow-trend-down"></i> {{ number_format($weeklyGrowth, 1) }}%
                </span>
            @else
                <span class="trend-percentage percentage-neutral">
                    <i class="fa-solid fa-minus"></i> 0%
                </span>
            @endif
        </div>
    </div>

    <!-- This Month vs Last Month -->
    <div class="trend-card">
        <span class="trend-period">Bulanan (Bulan Ini vs Bulan Lalu)</span>
        <div class="trend-comparison">
            <div class="trend-figures">
                <span class="trend-current"><span class="count-up" data-target="{{ $thisMonthTotal }}">0</span> <span style="font-size:0.75rem; font-weight:normal; color:var(--text-secondary);">kendaraan</span></span>
                <span class="trend-prev">Bulan Lalu: <span class="count-up" data-target="{{ $lastMonthTotal }}">0</span></span>
            </div>
            
            @if($monthlyGrowth > 0)
                <span class="trend-percentage percentage-up">
                    <i class="fa-solid fa-arrow-trend-up"></i> +{{ number_format($monthlyGrowth, 1) }}%
                </span>
            @elseif($monthlyGrowth < 0)
                <span class="trend-percentage percentage-down">
                    <i class="fa-solid fa-arrow-trend-down"></i> {{ number_format($monthlyGrowth, 1) }}%
                </span>
            @else
                <span class="trend-percentage percentage-neutral">
                    <i class="fa-solid fa-minus"></i> 0%
                </span>
            @endif
        </div>
    </div>
</div>

<!-- 3. CHARTS SECTIONS -->
<div class="chart-container-2">
    <!-- Daily Trend Chart (Left, larger) -->
    <div class="card">
        <div class="card-title" style="margin-bottom: 0.5rem;">
            <i class="fa-solid fa-chart-area"></i>
            <span>Tren Lalu Lintas Harian (kend./hari)</span>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 1rem; margin-left: 1.75rem;">
            15 Hari Terakhir | Diupdate: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB
        </div>
        <div class="chart-box">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
    
    <!-- Monthly Trend Chart (Right, smaller) -->
    <div class="card">
        <div class="card-title" style="margin-bottom: 0.5rem;">
            <i class="fa-solid fa-calendar-days"></i>
            <span>Tren Bulanan (kend./bulan)</span>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 1rem; margin-left: 1.75rem;">
            3 Bulan Terakhir | Diupdate: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB
        </div>
        <div class="chart-box">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title" style="margin-bottom: 0.5rem;">
        <i class="fa-solid fa-chart-column"></i>
        <span>Tren Lalu Lintas Mingguan (kend./minggu)</span>
    </div>
    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 1rem; margin-left: 1.75rem;">
        8 Minggu Terakhir | Diupdate: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }} WIB
    </div>
    <div class="chart-box" style="height: 350px;">
        <canvas id="weeklyChart"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Global Chart.js defaults configuration
    function updateChartDefaults() {
        const style = getComputedStyle(document.documentElement);
        const textColor = style.getPropertyValue('--text-secondary').trim() || '#9ca3af';
        const gridColor = style.getPropertyValue('--chart-grid').trim() || 'rgba(255, 255, 255, 0.08)';
        
        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;
        Chart.defaults.font.family = "'Outfit', sans-serif";
    }
    updateChartDefaults();

    // --- COUNT UP ANIMATION ---
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.count-up');
        
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const duration = 1200; // ms
            const stepTime = Math.abs(Math.floor(duration / (target || 1))); // prevent div by zero
            
            let start = 0;
            // Use requestAnimationFrame for smoother performance on larger numbers
            const formatter = new Intl.NumberFormat('en-US'); // Will format with commas (e.g. 15,200)
            
            // If target is small, slow down animation step
            const increment = target > 100 ? Math.ceil(target / 45) : 1; 

            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    counter.innerText = formatter.format(target);
                    clearInterval(timer);
                } else {
                    counter.innerText = formatter.format(start);
                }
            }, 25);
        });
    });

    // --- 1. DAILY CHART ---
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    
    // Create gradients for lines
    const blueGradient = dailyCtx.createLinearGradient(0, 0, 0, 300);
    blueGradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
    blueGradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    const dailyLabels = @json($dailyLabels);
    const dailyCars = @json($dailyCars);
    const dailyMotos = @json($dailyMotos);
    const dailyBuses = @json($dailyBuses);
    const dailyTrucks = @json($dailyTrucks);
    
    // Calculate total daily for visual line
    const dailyTotals = dailyCars.map((val, idx) => val + dailyMotos[idx] + dailyBuses[idx] + dailyTrucks[idx]);

    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyLabels.length ? dailyLabels : ['Belum Ada Data'],
            datasets: [
                {
                    label: 'Total Kendaraan',
                    data: dailyTotals.length ? dailyTotals : [0],
                    borderColor: '#3b82f6',
                    backgroundColor: blueGradient,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                },
                {
                    label: 'Mobil',
                    data: dailyCars.length ? dailyCars : [0],
                    borderColor: '#10b981',
                    fill: false,
                    tension: 0.3,
                    borderWidth: 1.5,
                    pointRadius: 2,
                },
                {
                    label: 'Motor',
                    data: dailyMotos.length ? dailyMotos : [0],
                    borderColor: '#f59e0b',
                    fill: false,
                    tension: 0.3,
                    borderWidth: 1.5,
                    pointRadius: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, padding: 15 } }
            },
            scales: {
                y: { beginAtZero: true, grid: { drawBorder: false } },
                x: { grid: { display: false } }
            }
        }
    });

    // --- 2. MONTHLY CHART ---
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    
    const monthlyLabels = @json($monthlyLabels);
    const monthlyCars = @json($monthlyCars);
    const monthlyMotos = @json($monthlyMotos);
    const monthlyBuses = @json($monthlyBuses);
    const monthlyTrucks = @json($monthlyTrucks);

    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels.length ? monthlyLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei'],
            datasets: [
                {
                    label: 'Mobil',
                    data: monthlyCars.length ? monthlyCars : [0, 0, 0, 0, 0],
                    backgroundColor: '#10b981',
                    borderRadius: 4
                },
                {
                    label: 'Motor',
                    data: monthlyMotos.length ? monthlyMotos : [0, 0, 0, 0, 0],
                    backgroundColor: '#f59e0b',
                    borderRadius: 4
                },
                {
                    label: 'Bus/Truk',
                    data: monthlyCars.length ? monthlyCars.map((val, idx) => (monthlyBuses[idx] || 0) + (monthlyTrucks[idx] || 0)) : [0, 0, 0, 0, 0],
                    backgroundColor: '#ef4444',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12 } }
            },
            scales: {
                y: { stacked: true, beginAtZero: true },
                x: { stacked: true, grid: { display: false } }
            }
        }
    });

    // --- 3. WEEKLY CHART ---
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    
    const weeklyLabels = @json($weeklyLabels);
    const weeklyCars = @json($weeklyCars);
    const weeklyMotos = @json($weeklyMotos);
    const weeklyBuses = @json($weeklyBuses);
    const weeklyTrucks = @json($weeklyTrucks);

    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: weeklyLabels.length ? weeklyLabels : ['Minggu 1', 'Minggu 2', 'Minggu 3'],
            datasets: [
                {
                    label: 'Mobil',
                    data: weeklyCars.length ? weeklyCars : [0],
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Motor',
                    data: weeklyMotos.length ? weeklyMotos : [0],
                    backgroundColor: 'rgba(245, 158, 11, 0.85)',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Bus',
                    data: weeklyBuses.length ? weeklyBuses : [0],
                    backgroundColor: 'rgba(139, 92, 246, 0.85)',
                    borderColor: '#8b5cf6',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Truk',
                    data: weeklyTrucks.length ? weeklyTrucks : [0],
                    backgroundColor: 'rgba(239, 68, 68, 0.85)',
                    borderColor: '#ef4444',
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, padding: 15 } }
            },
            scales: {
                y: { beginAtZero: true },
                x: { grid: { display: false } }
            }
        }
    });

    // --- LIVE CLOCK UPDATER ---
    setInterval(() => {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const formattedTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        
        document.querySelectorAll('.live-clock').forEach(el => {
            el.textContent = formattedTime;
        });
    }, 1000);
    
    // --- THEME CHANGE LISTENER ---
    window.addEventListener('themeChanged', () => {
        updateChartDefaults();
        
        const style = getComputedStyle(document.documentElement);
        const textColor = style.getPropertyValue('--text-secondary').trim();
        const gridColor = style.getPropertyValue('--chart-grid').trim();
        
        for (let id in Chart.instances) {
            const chart = Chart.instances[id];
            if (chart.options.scales) {
                if (chart.options.scales.x) {
                    chart.options.scales.x.ticks.color = textColor;
                    if(chart.options.scales.x.grid) chart.options.scales.x.grid.color = gridColor;
                }
                if (chart.options.scales.y) {
                    chart.options.scales.y.ticks.color = textColor;
                    if(chart.options.scales.y.grid) chart.options.scales.y.grid.color = gridColor;
                }
            }
            if (chart.options.plugins && chart.options.plugins.legend) {
                chart.options.plugins.legend.labels.color = textColor;
            }
            chart.update();
        }
    });

    // --- AUTO REFRESH TEPAT PADA MENIT KE +3 (Di detik 00) ---
    const now = new Date();
    // Target = 3 menit dari sekarang, tepat di detik ke-0
    const target = new Date(now);
    target.setMinutes(target.getMinutes() + 3);
    target.setSeconds(0);
    target.setMilliseconds(0);

    const msUntilTarget = target.getTime() - now.getTime();

    setTimeout(() => {
        window.location.reload();
    }, msUntilTarget);
</script>
@endsection
