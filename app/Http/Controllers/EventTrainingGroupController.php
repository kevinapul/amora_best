<?php

namespace App\Http\Controllers;

use App\Models\EventTrainingGroup;
use Illuminate\Http\Request;

class EventTrainingGroupController extends Controller
{
    /* ================== EDIT GROUP ================== */
    public function edit(EventTrainingGroup $group)
    {
        $this->authorize('update', $group);

        $group->load('events.training');

        return view('event_training_group.edit', [
            'group' => $group
        ]);
    }

public function approve(EventTrainingGroup $group)
{
    $this->authorize('approve', $group);

    foreach ($group->events as $event) {
        if ($event->status === 'pending') {
            $event->update(['status' => 'active']);
        }
    }

    return back()->with('success', 'Semua event dalam grup berhasil di-ACC marketing');
}


    /* ================== UPDATE GROUP ================== */
    public function update(Request $request, EventTrainingGroup $group)
    {
        $this->authorize('update', $group);

        $request->validate([
            'job_number'    => 'nullable|string|unique:event_training_groups,job_number,' . $group->id,
            'training_type' => 'required|in:reguler,inhouse',
            'harga_paket'   => 'nullable|numeric|min:0',

            'tempat'            => 'nullable|string',
            'jenis_sertifikasi' => 'nullable|string',
            'sertifikasi'       => 'nullable|string',
        ]);

        $group->update([
            'job_number'        => $request->job_number,
            'training_type'     => $request->training_type,
            'harga_paket'       => $request->training_type === 'inhouse'
                ? $request->harga_paket
                : null,

            'tempat'            => $request->tempat,
            'jenis_sertifikasi' => $request->jenis_sertifikasi,
            'sertifikasi'       => $request->sertifikasi,
        ]);

        return redirect()
            ->route('event-training.index')
            ->with('success', 'Data grup event berhasil diperbarui');
    }
// EventTrainingGroupController
public function show(EventTrainingGroup $group)
{
    $group->load([
        'masterTraining',
        'events.training'
    ]);

    return view('event_training_group.show', compact('group'));
}

public function addPayment(float $amount): void
{
    if (! $this->isInhouse()) {
        throw new \Exception('addPayment hanya untuk INHOUSE');
    }

    if ($amount <= 0) {
        throw new \Exception('Jumlah pembayaran tidak valid');
    }

    $totalPaid = $this->totalLunas();
    $newTotal  = $totalPaid + $amount;

    if ($newTotal > $this->totalTagihan()) {
        throw new \Exception('Pembayaran melebihi harga paket');
    }

    // 🔥 SIMPAN KE SEMUA EVENT → 1 EVENT JADI HOLDER
    $event = $this->events()->firstOrFail();

    foreach ($event->participants as $p) {
        $p->pivot->update([
            'paid_amount'      => $newTotal,
            'remaining_amount' => max(0, $this->totalTagihan() - $newTotal),
            'is_paid'          => $newTotal >= $this->totalTagihan(),
        ]);
    }
}


}
