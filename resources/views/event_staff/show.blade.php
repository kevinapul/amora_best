<x-app-layout>

        <div class="max-w-3xl mx-auto mt-10 bg-white p-6 shadow-md rounded-lg">

            <h1 class="text-2xl font-bold mb-4">Daftar Staff Event</h1>

            <div class="mb-6">
                <p><strong>Nama Event:</strong> {{ $event->training->name }}</p>
                <p><strong>Tanggal Event:</strong> {{ $event->tanggal }}</p>
            </div>

            <a href="{{ route('event-staff.create', $event->id) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Tambah Staff
            </a>

            <div class="mt-6">
                @if($staff->count() > 0)
                    @foreach($staff as $s)
                        <div class="border p-4 rounded mb-4 bg-gray-50">
                            <p><strong>Nama:</strong> {{ $s->name }}</p>
                            <p><strong>Nomor HP:</strong> {{ $s->phone ?? '-' }}</p>
                            <p><strong>Role:</strong> {{ $s->role }}</p>

                            <form action="{{ route('event-staff.destroy', $s->id) }}" method="POST" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500">Belum ada staff ditambahkan.</p>
                @endif
            </div>

        </div>

</x-app-layout>
