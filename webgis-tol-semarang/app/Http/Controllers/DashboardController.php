<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Fetch all active cameras
        $cameras = Camera::where('status', true)->get();
        
        return view('dashboard', compact('cameras'));
    }

    public function grid()
    {
        $cameras = Camera::where('status', true)->get();
        return view('grid', compact('cameras'));
    }
}
