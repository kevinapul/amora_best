@extends('layouts.app')

@section('content')
<h2 class="text-xl font-bold mb-3">
    Sertifikat untuk Event: {{ $event->training->name }}
</h2>

<a href="{{ route('certificates.create', $event->id) }}" class="btn btn-primary mb-3">+ Tambah Sertifikat</a>

<table class="table-auto w-full border">
    <thead>
        <tr>
            <th class="border px-3 py-2">Nama Peserta</th>
            <th class="border px-3 py-2">Nomor Sertifikat</th>
            <th class="border px-3 py-2">Tanggal Terbit</th>
            <th class="border px-3 py-2">Expired</th>
            <th class="border px-3 py-2">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($certificates as $cert)
        <tr>
            <td class="border px-3 py-2">{{ $cert->participant->nama }}</td>
            <td class="border px-3 py-2">{{ $cert->nomor_sertifikat }}</td>
            <td class="border px-3 py-2">{{ $cert->tanggal_terbit }}</td>
            <td class="border px-3 py-2">{{ $cert->tanggal_expired }}</td>
            <td class="border px-3 py-2">
                <form action="{{ route('certificates.destroy', $cert->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('Hapus sertifikat?')">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
