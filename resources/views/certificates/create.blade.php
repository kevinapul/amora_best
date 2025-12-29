@extends('layouts.app')

@section('content')
<h2 class="text-xl font-bold mb-3">Tambah Sertifikat</h2>

<form action="{{ route('certificates.store') }}" method="POST">
    @csrf

    <input type="hidden" name="event_training_id" value="{{ $event->id }}">

    <div class="mb-3">
        <label>Peserta</label>
        <select name="participant_id" class="form-control" required>
            <option value="">-- Pilih Peserta --</option>
            @foreach($participants as $p)
                <option value="{{ $p->id }}">{{ $p->nama }} — {{ $p->perusahaan }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Nomor Sertifikat</label>
        <input type="text" name="nomor_sertifikat" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Tanggal Terbit</label>
        <input type="date" name="tanggal_terbit" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Tanggal Expired</label>
        <input type="date" name="tanggal_expired" class="form-control">
    </div>

    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
