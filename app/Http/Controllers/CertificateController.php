<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventTraining;
use App\Models\Participant;
use App\Models\Certificate;
use Carbon\Carbon;

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



        public function detail(Participant $participant, EventTraining $event)
    {
        $certificate = Certificate::firstOrNew([
            'participant_id'    => $participant->id,
            'event_training_id' => $event->id
        ]);

        return view('certificate.detail', compact(
            'participant',
            'event',
            'certificate'
        ));
    }


    /* ===============================
     * SAVE CERTIFICATE
     * =============================== */
    public function save(Request $request, Participant $participant, EventTraining $event)
    {
        $request->validate([
            'nomor_sertifikat'   => 'required|string|max:100',
            'tanggal_terbit'     => 'required|date',
            'masa_berlaku_tahun' => 'nullable|integer|min:1',
            'file'               => 'nullable|file|mimes:pdf|max:5120',
        ]);

        /* ===== AMBIL PIVOT ===== */
        $pivot = $event->participants()
            ->where('participants.id', $participant->id)
            ->first()
            ?->pivot;

        if (!$pivot) {
            return back()->with('error', 'Peserta tidak terdaftar di event ini');
        }

        $companyId = $pivot->company_id;

        /* ===== HITUNG EXPIRED ===== */
        $expired = null;

        if ($request->masa_berlaku_tahun) {
            $expired = Carbon::parse($request->tanggal_terbit)
                ->addYears($request->masa_berlaku_tahun);
        }

        /* ===== CEK CERTIFICATE EXISTING ===== */
        $certificate = Certificate::firstOrNew([
            'participant_id'    => $participant->id,
            'event_training_id' => $event->id,
        ]);

        /* ===== FILE UPLOAD ===== */
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')
                ->store('certificates', 'public');

            $certificate->file_path = $filePath;
        }

        /* ===== SAVE DATA ===== */
        $certificate->nomor_sertifikat   = $request->nomor_sertifikat;
        $certificate->tanggal_terbit     = $request->tanggal_terbit;
        $certificate->masa_berlaku_tahun = $request->masa_berlaku_tahun;
        $certificate->tanggal_expired    = $expired;
        $certificate->company_id         = $companyId;
        $certificate->input_by           = auth()->id();
        $certificate->status             = 'active';

        $certificate->save();

        /* ===== UPDATE PIVOT ===== */
        $pivot->update([
            'certificate_ready'      => true,
            'certificate_issued_at'  => now(),
        ]);

        return redirect()->back()->with('success', 'Sertifikat berhasil disimpan');
    }


}
