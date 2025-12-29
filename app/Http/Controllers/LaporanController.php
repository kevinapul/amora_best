<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewLaporan', EventTraining::class);

        // ================== FILTER PERIODE ==================
        $range = (int) $request->get('range', 30); // default 30 hari
        $range = in_array($range, [30, 90, 180, 360]) ? $range : 30;

        $fromDate = now()->subDays($range)->startOfDay();

        // ================== BASE QUERY ==================
        $baseQuery = EventTraining::with(['training', 'participants'])
            ->where('status', 'done')
            ->where('tanggal_end', '>=', $fromDate);

        // ================== TABEL A (DONE & LUNAS) ==================
        $eventsLunas = (clone $baseQuery)
            ->where('finance_approved', true)
            ->get()
            ->map(function ($event) {
                $totalPendapatan = $event->jenis_event === 'inhouse'
                    ? $event->harga_paket
                    : $event->participants->sum(fn ($p) => $p->pivot->harga_peserta);

                return (object) [
                    'event'            => $event,
                    'total_peserta'    => $event->participants->count(),
                    'total_pendapatan' => $totalPendapatan,
                    'approved_at'      => $event->finance_approved_at,
                ];
            });

        // ================== TABEL B (DONE BELUM LUNAS) ==================
        $eventsBelumLunas = (clone $baseQuery)
            ->where('finance_approved', false)
            ->get()
            ->map(function ($event) {
                $totalTagihan = $event->jenis_event === 'inhouse'
                    ? $event->harga_paket
                    : $event->participants->sum(fn ($p) => $p->pivot->harga_peserta);

                $totalLunas = $event->participants
                    ->where('pivot.is_paid', true)
                    ->sum(fn ($p) => $p->pivot->harga_peserta);

                return (object) [
                    'event'         => $event,
                    'total_peserta' => $event->participants->count(),
                    'total_tagihan' => $totalTagihan,
                    'total_lunas'   => $totalLunas,
                    'sisa'          => $totalTagihan - $totalLunas,
                ];
            });

        // ================== SUMMARY ==================
        $summary = [
            'total_event_done'       => $baseQuery->count(),
            'total_event_lunas'      => $eventsLunas->count(),
            'total_event_belum'      => $eventsBelumLunas->count(),
            'total_pendapatan'       => $eventsLunas->sum('total_pendapatan'),
        ];

        return view('laporan.index', compact(
            'range',
            'summary',
            'eventsLunas',
            'eventsBelumLunas'
        ));
    }
}
