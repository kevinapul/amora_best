<x-app-layout>
<div class="max-w-3xl mx-auto py-12">
    <h1 class="text-2xl font-bold mb-6">
        Tambah Peserta - {{ $event->training->code }}
    </h1>

    <form action="{{ route('event-participant.store', $event) }}" method="POST">
        @csrf
        <input type="hidden" name="event_training_id" value="{{ $event->id }}">

        <div class="mb-4">
            <label class="block font-medium">Nama</label>
            <input type="text" name="nama" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Perusahaan</label>
            <input type="text" name="perusahaan" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Email</label>
            <input type="email" name="email" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block font-medium">No HP</label>
            <input type="text" name="no_hp" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Keterangan</label>
            <textarea name="keterangan" class="w-full border p-2 rounded"></textarea>
        </div>

        {{-- MUNCUL HANYA JIKA REGULER --}}
        @if ($event->jenis_event === 'reguler')
            <div class="mb-4">
                <label class="block font-medium">Harga Peserta</label>
                <input type="number" name="harga_peserta" class="w-full border p-2 rounded" required>
            </div>
        @endif

        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">
            Simpan Peserta
        </button>
    </form>
</div>
</x-app-layout>
