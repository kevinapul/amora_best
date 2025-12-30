<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function store(Request $request, EventTraining $event, Participant $participant)
    {
        /* ================= VALIDATION ================= */

        $request->validate([
            'nomor_sertifikat' => 'required|string|max:100',
            'tanggal_terbit'   => 'required|date',
        ]);

        /* ================= GUARD ================= */

        if (! $event->canInputCertificate()) {
            return back()->with('error', 'Event belum boleh input sertifikat');
        }

        $pivot = $event->participants()
            ->where('participants.id', $participant->id)
            ->first()
            ?->pivot;

        if (! $pivot) {
            return back()->with('error', 'Peserta tidak terdaftar di event ini');
        }

        if ($event->isReguler() && ! $pivot->isPaid()) {
            return back()->with('error', 'Peserta belum melakukan pembayaran');
        }

        /* ================= HITUNG EXPIRED ================= */

        $years = $event->certificateValidityYears();

        $expiredAt = $years
            ? \Carbon\Carbon::parse($request->tanggal_terbit)->addYears($years)
            : null;

        /* ================= INPUT / UPDATE ================= */

        Certificate::updateOrCreate(
            [
                'event_training_id' => $event->id,
                'participant_id'    => $participant->id,
            ],
            [
                'nomor_sertifikat' => $request->nomor_sertifikat,
                'tanggal_terbit'   => $request->tanggal_terbit,
                'tanggal_expired'  => $expiredAt,
            ]
        );

        /* ================= PIVOT ================= */

        $pivot->markCertificateReady();

        return back()->with('success', 'Data sertifikat berhasil dicatat');
    }
}
