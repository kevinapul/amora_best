<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();

        /* ================= EVENT BULAN INI ================= */
        $baseQuery = EventTraining::with(['training', 'eventTrainingGroup'])
            ->withCount('participants')
            ->where('status', '!=', 'done')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal_start', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('tanggal_end', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                      $q->where('tanggal_start', '<=', $startOfMonth)
                        ->where('tanggal_end', '>=', $endOfMonth);
                  });
            });

        $reguler = (clone $baseQuery)
            ->whereHas('eventTrainingGroup', fn ($q) =>
                $q->where('training_type', 'reguler')
            )
            ->orderBy('tanggal_start')
            ->get();

        $inhouse = (clone $baseQuery)
            ->whereHas('eventTrainingGroup', fn ($q) =>
                $q->where('training_type', 'inhouse')
            )
            ->orderBy('tanggal_start')
            ->get();

        /* ================= REALISASI ================= */
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear   = Carbon::now()->endOfYear();

        $inhouseTotal = EventTraining::whereIn('status', ['active', 'on_progress', 'done'])
    ->whereBetween('tanggal_end', [$startOfYear, $endOfYear])
    ->whereHas('eventTrainingGroup', fn ($q) =>
        $q->where('training_type', 'inhouse')
    )
    ->sum(DB::raw('(
        select harga_paket
        from event_training_groups
        where event_training_groups.id = event_trainings.event_training_group_id
    )'));

$regulerTotal = DB::table('event_participants')
    ->join('event_trainings', 'event_trainings.id', '=', 'event_participants.event_training_id')
    ->join('event_training_groups', 'event_training_groups.id', '=', 'event_trainings.event_training_group_id')
    ->whereIn('event_trainings.status', ['active', 'on_progress', 'done'])
    ->whereBetween('event_trainings.tanggal_end', [$startOfYear, $endOfYear])
    ->where('event_training_groups.training_type', 'reguler')
    ->sum('event_participants.harga_peserta');

$totalRealisasi = $inhouseTotal + $regulerTotal;

        /* ================= TARGET (OWNER ONLY) ================= */
        $targetBawah = null;
        $targetTengah = null;
        $targetAtas = null;
        $statusTarget = null;

        if ($user->role === 'it') { // OWNER
            $targetBawah  = 100_000_000;
            $targetTengah = 200_000_000;
            $targetAtas   = 300_000_000;

            $statusTarget =
                $totalRealisasi >= $targetAtas
                    ? 'Target Atas Tercapai'
                    : ($totalRealisasi >= $targetTengah
                        ? 'Target Tengah Tercapai'
                        : ($totalRealisasi >= $targetBawah
                            ? 'Target Bawah Tercapai'
                            : 'Target Belum Tercapai'));
        }

        $attendanceToday = Attendance::where('user_id', $user->id)
            ->latest()
            ->first();

        return view('dashboard', compact(
            'reguler',
            'inhouse',
            'attendanceToday',
            'totalRealisasi',
            'targetBawah',
            'targetTengah',
            'targetAtas',
            'statusTarget'
        ));
    }
}
