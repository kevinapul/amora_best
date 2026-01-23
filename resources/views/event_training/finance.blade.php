<x-app-layout>
<x-slot name="header">
    <h2 class="text-xl font-semibold text-gray-800">
        Finance – Pembayaran Event
    </h2>
</x-slot>

@php
    $totalTagihan = $event->isInhouse()
        ? $event->eventTrainingGroup->harga_paket
        : $event->participants->sum(fn ($p) => $p->pivot->harga_peserta);

    $totalLunas = $event->participants
        ->sum(fn ($p) => $p->pivot->paid_amount ?? 0);

    $sisa = $totalTagihan - $totalLunas;
@endphp

<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

{{-- ================= SUMMARY ================= --}}
<div class="bg-white rounded shadow p-6 grid grid-cols-3 gap-6">
    <div>
        <p class="text-sm text-gray-500">Total Tagihan</p>
        <p class="text-xl font-bold text-indigo-600">
            Rp {{ number_format($totalTagihan,0,',','.') }}
        </p>
    </div>

    <div>
        <p class="text-sm text-gray-500">Sudah Dibayar</p>
        <p class="text-xl font-bold text-green-600">
            Rp {{ number_format($totalLunas,0,',','.') }}
        </p>
    </div>

    <div>
        <p class="text-sm text-gray-500">Sisa</p>
        <p class="text-xl font-bold text-red-600">
            Rp {{ number_format($sisa,0,',','.') }}
        </p>
    </div>
</div>

<form method="POST" action="{{ route('event-training.bulk-payment', $event->id) }}">
@csrf

{{-- ================= PILIH PERUSAHAAN ================= --}}
<div class="bg-white rounded shadow p-6">
    <label class="block font-semibold mb-2">Pilih Perusahaan / Individu</label>

    <select name="company" id="companySelect"
            class="w-full border rounded p-2">
        <option value="">-- pilih --</option>
        <option value="INDIVIDU">INDIVIDU</option>

        @foreach($companies as $company => $items)
            @if($company !== 'INDIVIDU')
                @php
                    $total = $items->sum(fn ($p) => $p->pivot->harga_peserta);
                    $paid  = $items->sum(fn ($p) => $p->pivot->paid_amount ?? 0);
                @endphp
                <option value="{{ $company }}">
                    {{ $company }} — {{ number_format($paid,0,',','.') }}
                    / {{ number_format($total,0,',','.') }}
                </option>
            @endif
        @endforeach
    </select>
</div>

{{-- ================= TABEL PESERTA ================= --}}
<div class="bg-white rounded shadow overflow-hidden">
<table class="w-full text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="px-3 py-2"></th>
            <th class="px-3 py-2 text-left">Nama</th>
            <th class="px-3 py-2 text-left">Perusahaan</th>
            <th class="px-3 py-2 text-right">Harga</th>
            <th class="px-3 py-2 text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($event->participants as $p)
        <tr class="border-t participant-row"
            data-company="{{ $p->perusahaan ?? 'INDIVIDU' }}">
            <td class="px-3 py-2 text-center">
                <input type="checkbox"
                       name="participants[]"
                       value="{{ $p->id }}"
                       class="participant-check hidden">
            </td>
            <td class="px-3 py-2">{{ $p->nama }}</td>
            <td class="px-3 py-2">{{ $p->perusahaan ?? 'INDIVIDU' }}</td>
            <td class="px-3 py-2 text-right">
                Rp {{ number_format($p->pivot->harga_peserta,0,',','.') }}
            </td>
            <td class="px-3 py-2 text-center">
                @if(($p->pivot->remaining_amount ?? $p->pivot->harga_peserta) <= 0)
                    <span class="text-green-600 font-semibold">LUNAS</span>
                @else
                    <span class="text-red-600">BELUM</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

{{-- ================= INPUT BAYAR ================= --}}
<div class="bg-white rounded shadow p-6">
    <label class="block font-semibold mb-1">Jumlah Dibayarkan</label>
    <input type="number" name="amount"
           class="w-full border rounded p-2"
           placeholder="Contoh: 5000000">
    <p class="text-xs text-gray-500 mt-1">
        Untuk INDIVIDU, jumlah bebas (per orang). Untuk perusahaan = total.
    </p>
</div>

{{-- ================= ACTION ================= --}}
<div class="flex justify-end gap-3">
    <a href="{{ route('event-training.show', $event->id) }}"
       class="px-4 py-2 bg-gray-500 text-white rounded">Batal</a>

    <button type="submit"
            class="px-4 py-2 bg-green-600 text-white rounded">
        Simpan Pembayaran
    </button>
</div>

</form>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
const select = document.getElementById('companySelect');
const rows   = document.querySelectorAll('.participant-row');

select.addEventListener('change', () => {
    const val = select.value;

    rows.forEach(row => {
        const chk = row.querySelector('.participant-check');

        if (!val) {
            row.classList.add('hidden');
            chk.classList.add('hidden');
            chk.checked = false;
            return;
        }

        if (row.dataset.company === val) {
            row.classList.remove('hidden');

            if (val === 'INDIVIDU') {
                chk.classList.remove('hidden');
            } else {
                chk.classList.add('hidden');
                chk.checked = false;
            }
        } else {
            row.classList.add('hidden');
            chk.classList.add('hidden');
            chk.checked = false;
        }
    });
});
</script>

</x-app-layout>
