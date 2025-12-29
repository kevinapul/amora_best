<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Event Training
        </h2>
    </x-slot>

@php
    try {
        $start = $event->tanggal_start
            ? \Carbon\Carbon::parse($event->tanggal_start)->translatedFormat('d F Y')
            : null;

        $end = $event->tanggal_end
            ? \Carbon\Carbon::parse($event->tanggal_end)->translatedFormat('d F Y')
            : null;

        $tanggal = ($start && $end)
            ? ($start === $end ? $start : "$start - $end")
            : '-';
    } catch (\Exception $ex) {
        $tanggal = $event->tanggal_start . ' - ' . $event->tanggal_end;
    }
@endphp

<div class="py-12">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

{{-- ================= INFO EVENT ================= --}}
<div class="bg-white shadow-md sm:rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold mb-2">
        {{ $event->training->name }} ({{ $event->training->code }})
    </h3>

    <p><strong>Job Number:</strong> {{ $event->job_number }}</p>
    <p><strong>Tanggal:</strong> {{ $tanggal }}</p>
    <p><strong>Tempat:</strong> {{ $event->tempat }}</p>
    <p><strong>Jenis Event:</strong> {{ strtoupper($event->jenis_event) }}</p>
    <p><strong>Sertifikasi:</strong> {{ $event->jenis_sertifikasi }}</p>

    @if ($event->jenis_event === 'reguler')
        <p class="mt-2">
            <strong>Total Harga:</strong>
            Rp {{ number_format($event->participants->sum(fn($p) => $p->pivot->harga_peserta),0,',','.') }}
        </p>
    @else
        <p class="mt-2">
            <strong>Total Harga:</strong>
            Rp {{ number_format($event->harga_paket,0,',','.') }}
        </p>
    @endif

    <p class="mt-2">
        <span class="px-2 py-1 rounded text-white font-semibold
            {{ $event->status === 'done' ? 'bg-green-600' : ($event->status === 'active' ? 'bg-blue-600' : 'bg-gray-500') }}">
            {{ strtoupper($event->status) }}
        </span>
    </p>
</div>

{{-- ================= INHOUSE FINANCE ================= --}}
@if($event->jenis_event === 'inhouse')

    @can('approveFinance', $event)
        @if($event->status === 'done' && !$event->finance_approved)
            <form method="POST"
                  action="{{ route('event-training.approveFinance', $event->id) }}"
                  class="mb-4">
                @csrf
                <button class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800">
                    ACC Finance (Inhouse)
                </button>
            </form>
        @endif
    @endcan

    <div class="mb-6">
        @if($event->status !== 'done')
            <span class="px-3 py-1 rounded bg-blue-500 text-white font-semibold">
                ⏳ Event masih berjalan
            </span>
        @elseif($event->finance_approved)
            <span class="px-3 py-1 rounded bg-green-600 text-white font-semibold">
                ✔ Finance sudah ACC (Lunas)
            </span>
        @else
            <span class="px-3 py-1 rounded bg-yellow-500 text-white font-semibold">
                Menunggu ACC Finance
            </span>
        @endif
    </div>

@endif

{{-- ================= BUTTON TAMBAH PESERTA ================= --}}
@can('addParticipant', $event)
<div class="mb-4">
    <a href="{{ route('event-participant.create', $event->id) }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
        Tambah Peserta
    </a>
</div>
@endcan

{{-- ================= DAFTAR PESERTA ================= --}}
<div class="bg-white shadow-md sm:rounded-lg p-6">
<h3 class="text-lg font-semibold mb-4">Daftar Peserta</h3>

@if($event->participants->count())
<table class="w-full border border-gray-300 text-sm">
<thead class="bg-gray-100">
<tr>
    <th class="border px-3 py-2">No</th>
    <th class="border px-3 py-2">Nama</th>
    <th class="border px-3 py-2">Perusahaan</th>
    <th class="border px-3 py-2">No HP</th>

    @if($event->jenis_event === 'reguler')
        <th class="border px-3 py-2 text-right">Harga</th>
        <th class="border px-3 py-2 text-center">Status Bayar</th>
    @endif
</tr>
</thead>

<tbody>
@foreach($event->participants as $i => $participant)
<tr>
    <td class="border px-3 py-2 text-center">{{ $i + 1 }}</td>
    <td class="border px-3 py-2">{{ $participant->nama }}</td>
    <td class="border px-3 py-2">{{ $participant->perusahaan ?? '-' }}</td>
    <td class="border px-3 py-2">{{ $participant->no_hp ?? '-' }}</td>

    @if($event->jenis_event === 'reguler')
        <td class="border px-3 py-2 text-right">
            Rp {{ number_format($participant->pivot->harga_peserta,0,',','.') }}
        </td>
        <td class="border px-3 py-2 text-center">
            @if($participant->pivot->is_paid)
                <span class="px-2 py-1 rounded bg-green-600 text-white font-semibold">✔ Lunas</span>
            @else
                <span class="px-2 py-1 rounded bg-red-600 text-white font-semibold">Belum</span>
            @endif
        </td>
    @endif
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-600">Belum ada peserta.</p>
@endif
</div>

{{-- ================= REGULER PAYMENT ACTION ================= --}}
@if(
    $event->jenis_event === 'reguler' &&
    $event->status === 'done' &&
    !$event->finance_approved
)
@can('updateFinance', $event)
<div class="mt-6 bg-white shadow-md rounded p-6">
    <h4 class="font-semibold mb-3">Tandai Pembayaran Peserta</h4>

    @foreach($event->participants->where(fn($p) => !$p->pivot->is_paid) as $participant)
        <form method="POST"
              action="{{ route('event-participant.markPaid', [$event->id, $participant->id]) }}"
              class="mb-2">
            @csrf
            <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Tandai Lunas: {{ $participant->nama }}
            </button>
        </form>
    @endforeach
</div>
@endcan
@endif

</div>
</div>
</x-app-layout>
