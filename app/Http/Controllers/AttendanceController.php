<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * CHECK-IN
     */
    public function store(Request $request)
    {
        $request->validate([
            'activity' => 'required|string'
        ]);

        $userId = Auth::id();

        // Cek apakah hari ini sudah ada absen
        $today = Attendance::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->first();

        // ✔ SUDAH CHECK-IN & SUDAH CHECK-OUT → TIDAK BOLEH CHECK-IN LAGI
        if ($today && $today->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah menyelesaikan pekerjaan hari ini.',
            ]);
        }

        // ✔ SUDAH CHECK-IN & BELUM CHECK-OUT → TOLAK CHECK-IN LAGI
        if ($today && !$today->clock_out) {
            return response()->json([
                'success' => true,
                'checked_in' => true,
                'attendance' => $today,
            ]);
        }

        // ✔ CHECK-IN BARU
        $attendance = Attendance::create([
            'user_id'  => $userId,
            'activity' => $request->activity
        ]);

        return response()->json([
            'success' => true,
            'checked_in' => true,
            'attendance' => $attendance
        ]);
    }


    /**
     * CHECK-OUT (Tombol OFF + Modal)
     */
    public function checkout()
{
    $userId = Auth::id();

    $attendance = Attendance::where('user_id', $userId)
        ->whereDate('created_at', today())
        ->first();

    if (!$attendance) {
        return response()->json(['success' => false, 'message' => 'Belum melakukan check-in hari ini.']);
    }

    if ($attendance->clock_out) {
        return response()->json(['success' => false, 'message' => 'Sudah checkout hari ini.']);
    }

    $attendance->update(['clock_out' => now()]);

    $duration = $attendance->created_at->diffInSeconds($attendance->clock_out);

    return response()->json(['success' => true, 'duration' => $duration]);
}
}
