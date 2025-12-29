<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">

        <h1 class="text-2xl font-bold mb-6">Event Training</h1>

        {{-- ACTION BAR --}}
        <div class="flex items-center justify-between mb-4">
            @can('create', \App\Models\EventTraining::class)
                <a href="{{ route('event-training.create') }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    + Tambah Event Training
                </a>
            @endcan

            <input type="text" id="search"
                   placeholder="Cari training / job number..."
                   class="border rounded px-3 py-2 w-64"
                   value="{{ $search ?? '' }}">
        </div>

        {{-- ===================== --}}
        {{-- EVENT AKTIF --}}
        {{-- ===================== --}}
        <h2 class="text-lg font-semibold mb-2">📌 Event Aktif</h2>

        <div id="table-container">
            @include('event_training.table-active', [
                'events' => $eventsActive
            ])
        </div>

        {{-- ===================== --}}
        {{-- EVENT PENDING --}}
        {{-- ===================== --}}
        @if($eventsPending)
            <h2 class="text-lg font-semibold mt-10 mb-2 text-yellow-700">
                ⏳ Event Pending (Menunggu ACC)
            </h2>

            @include('event_training.table-pending', [
                'events' => $eventsPending
            ])
        @endif

    </div>

    {{-- LIVE SEARCH --}}
    <script>
        const searchInput = document.getElementById('search');
        searchInput.addEventListener('input', function () {
            const keyword = this.value;

            fetch("{{ route('event-training.index') }}?search=" + keyword)
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('table-container').innerHTML =
                        doc.querySelector('#table-container').innerHTML;
                });
        });
    </script>
</x-app-layout>
