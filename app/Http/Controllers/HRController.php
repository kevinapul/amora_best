<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class HRController extends Controller
{
    public function index()
    {
        // Users yang sedang aktif (check-in hari ini & belum checkout)
        $activeUsers = Attendance::whereDate('created_at', today())
            ->whereNull('clock_out')
            ->with('user')
            ->get()
            ->map(function($att) {
                return (object)[
                    'id' => $att->user->id,
                    'name' => $att->user->name,
                    'check_in' => $att->created_at,
                ];
            });

        // Users yang belum absen hari ini
        $allUsers = User::all();
        $pendingUsers = $allUsers->filter(function($user) {
            return !Attendance::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->exists();
        });

        return view('division.hr', compact('activeUsers', 'pendingUsers'));
    }

    public function forceCheckout($userId)
    {
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->whereNull('clock_out')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'User belum check-in atau sudah checkout.');
        }

        $attendance->update(['clock_out' => now()]);

        return back()->with('success', 'User berhasil di-force checkout.');
    }
}
