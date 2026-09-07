<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\VehicleStatisticsDaily;
use App\Models\VehicleStatisticsWeekly;
use App\Models\VehicleStatisticsMonthly;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $cameraId = $request->input('camera_id');
        $cameras = Camera::all();

        // Base query scopes
        $dailyQuery = VehicleStatisticsDaily::query();
        $weeklyQuery = VehicleStatisticsWeekly::query();
        $monthlyQuery = VehicleStatisticsMonthly::query();

        if ($cameraId) {
            $dailyQuery->where('camera_id', $cameraId);
            $weeklyQuery->where('camera_id', $cameraId);
            $monthlyQuery->where('camera_id', $cameraId);
        }

        // --- 1. OVERALL TOTALS (Current Month) ---
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $statsSummary = (clone $dailyQuery)
            ->whereMonth('statistic_date', $currentMonth)
            ->whereYear('statistic_date', $currentYear)
            ->select(
                DB::raw('SUM(motorcycle) as motorcycle'),
                DB::raw('SUM(car) as car'),
                DB::raw('SUM(bus) as bus'),
                DB::raw('SUM(truck) as truck'),
                DB::raw('SUM(total) as total')
            )->first();

        // Fallbacks if null
        $motorcycleCount = $statsSummary->motorcycle ?? 0;
        $carCount = $statsSummary->car ?? 0;
        $busCount = $statsSummary->bus ?? 0;
        $truckCount = $statsSummary->truck ?? 0;
        $totalCount = $statsSummary->total ?? 0;

        // --- 1.1 OVERALL TOTALS (Today) ---
        $todayDate = Carbon::today();
        $todayStatsSummary = (clone $dailyQuery)
            ->where('statistic_date', $todayDate)
            ->select(
                DB::raw('SUM(motorcycle) as motorcycle'),
                DB::raw('SUM(car) as car'),
                DB::raw('SUM(bus) as bus'),
                DB::raw('SUM(truck) as truck')
            )->first();
            
        $todayMotorcycleCount = $todayStatsSummary->motorcycle ?? 0;
        $todayCarCount = $todayStatsSummary->car ?? 0;
        $todayBusCount = $todayStatsSummary->bus ?? 0;
        $todayTruckCount = $todayStatsSummary->truck ?? 0;

        // --- 2. AUTOMATIC TREND INDICATORS ---
        // Today vs Yesterday
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayTotal = (clone $dailyQuery)->where('statistic_date', $today)->sum('total');
        $yesterdayTotal = (clone $dailyQuery)->where('statistic_date', $yesterday)->sum('total');
        $dailyGrowth = $yesterdayTotal > 0 ? (($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100 : 0;

        // This Week vs Last Week
        $thisWeekNum = Carbon::now()->weekOfYear;
        $thisWeekYear = Carbon::now()->year;
        $lastWeekCarbon = Carbon::now()->subWeek();
        $lastWeekNum = $lastWeekCarbon->weekOfYear;
        $lastWeekYear = $lastWeekCarbon->year;

        $thisWeekTotal = (clone $weeklyQuery)->where('week', $thisWeekNum)->where('year', $thisWeekYear)->sum('total');
        $lastWeekTotal = (clone $weeklyQuery)->where('week', $lastWeekNum)->where('year', $lastWeekYear)->sum('total');
        $weeklyGrowth = $lastWeekTotal > 0 ? (($thisWeekTotal - $lastWeekTotal) / $lastWeekTotal) * 100 : 0;

        // This Month vs Last Month
        $thisMonthNum = Carbon::now()->month;
        $thisMonthYear = Carbon::now()->year;
        $lastMonthCarbon = Carbon::now()->subMonth();
        $lastMonthNum = $lastMonthCarbon->month;
        $lastMonthYear = $lastMonthCarbon->year;

        $thisMonthTotal = (clone $monthlyQuery)->where('month', $thisMonthNum)->where('year', $thisMonthYear)->sum('total');
        $lastMonthTotal = (clone $monthlyQuery)->where('month', $lastMonthNum)->where('year', $lastMonthYear)->sum('total');
        $monthlyGrowth = $lastMonthTotal > 0 ? (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : 0;

        // --- 3. CHART DATA ---
        // Daily Chart (Last 15 Days)
        $dailyChartData = (clone $dailyQuery)
            ->select('statistic_date', 
                DB::raw('SUM(car) as car'),
                DB::raw('SUM(motorcycle) as motorcycle'),
                DB::raw('SUM(bus) as bus'),
                DB::raw('SUM(truck) as truck')
            )
            ->groupBy('statistic_date')
            ->orderBy('statistic_date', 'desc')
            ->limit(15)
            ->get()
            ->reverse();

        $dailyLabels = [];
        $dailyCars = [];
        $dailyMotos = [];
        $dailyBuses = [];
        $dailyTrucks = [];
        foreach ($dailyChartData as $row) {
            $dailyLabels[] = Carbon::parse($row->statistic_date)->format('d M');
            $dailyCars[] = $row->car;
            $dailyMotos[] = $row->motorcycle;
            $dailyBuses[] = $row->bus;
            $dailyTrucks[] = $row->truck;
        }

        // Weekly Chart (Last 8 Weeks)
        $weeklyChartData = (clone $weeklyQuery)
            ->select('year', 'week',
                DB::raw('SUM(car) as car'),
                DB::raw('SUM(motorcycle) as motorcycle'),
                DB::raw('SUM(bus) as bus'),
                DB::raw('SUM(truck) as truck')
            )
            ->groupBy('year', 'week')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'desc')
            ->limit(8)
            ->get()
            ->reverse();

        $weeklyLabels = [];
        $weeklyCars = [];
        $weeklyMotos = [];
        $weeklyBuses = [];
        $weeklyTrucks = [];
        foreach ($weeklyChartData as $row) {
            $weeklyLabels[] = "Minggu " . $row->week . " (" . $row->year . ")";
            $weeklyCars[] = $row->car;
            $weeklyMotos[] = $row->motorcycle;
            $weeklyBuses[] = $row->bus;
            $weeklyTrucks[] = $row->truck;
        }

        // Monthly Chart (Latest 3 Months)
        $monthlyChartData = (clone $monthlyQuery)
            ->select('year', 'month',
                DB::raw('SUM(car) as car'),
                DB::raw('SUM(motorcycle) as motorcycle'),
                DB::raw('SUM(bus) as bus'),
                DB::raw('SUM(truck) as truck')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(3)
            ->get()
            ->reverse();

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $monthlyLabels = [];
        $monthlyCars = [];
        $monthlyMotos = [];
        $monthlyBuses = [];
        $monthlyTrucks = [];
        foreach ($monthlyChartData as $row) {
            $monthlyLabels[] = $monthNames[$row->month] ?? "Bulan " . $row->month;
            $monthlyCars[] = $row->car;
            $monthlyMotos[] = $row->motorcycle;
            $monthlyBuses[] = $row->bus;
            $monthlyTrucks[] = $row->truck;
        }

        return view('analytics', compact(
            'cameras',
            'cameraId',
            'motorcycleCount',
            'carCount',
            'busCount',
            'truckCount',
            'totalCount',
            'todayMotorcycleCount',
            'todayCarCount',
            'todayBusCount',
            'todayTruckCount',
            'todayTotal',
            'yesterdayTotal',
            'dailyGrowth',
            'thisWeekTotal',
            'lastWeekTotal',
            'weeklyGrowth',
            'thisMonthTotal',
            'lastMonthTotal',
            'monthlyGrowth',
            'dailyLabels',
            'dailyCars',
            'dailyMotos',
            'dailyBuses',
            'dailyTrucks',
            'weeklyLabels',
            'weeklyCars',
            'weeklyMotos',
            'weeklyBuses',
            'weeklyTrucks',
            'monthlyLabels',
            'monthlyCars',
            'monthlyMotos',
            'monthlyBuses',
            'monthlyTrucks'
        ));
    }
}
