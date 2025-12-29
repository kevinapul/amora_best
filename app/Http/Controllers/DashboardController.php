<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $month = now()->month;
        $year = now()->year;

        // Ambil TRAINING REGULER bulan ini
        $reguler = EventTraining::with(['training'])
            ->withCount('participants')
            ->where('jenis_event', 'reguler')
            ->whereMonth('tanggal_start', $month)
            ->whereYear('tanggal_start', $year)
            ->orderBy('tanggal_start', 'ASC')
            ->get();
        
        // Ambil TRAINING INHOUSE bulan ini
        $inhouse = EventTraining::with(['training'])
            ->withCount('participants')
            ->where('jenis_event', 'inhouse')
            ->whereMonth('tanggal_start', $month)
            ->whereYear('tanggal_start', $year)
            ->orderBy('tanggal_start', 'ASC')
            ->get();

        $attendanceToday = Attendance::where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->first();

        return view('dashboard', compact('reguler', 'inhouse', 'attendanceToday'));
    }
}
