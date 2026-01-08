<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">

        <h1 class="text-2xl font-bold mb-6">Event Training</h1>

        {{-- ================= ACTION BAR ================= --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">

            @can('create', \App\Models\EventTraining::class)
                <a href="{{ route('event-training.create') }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    + Tambah Event
                </a>
            @endcan

            <div class="flex gap-3 items-center">
                {{-- MODE --}}
                <select id="mode"
                        class="border rounded px-3 py-2">
                    <option value="training" {{ $mode === 'training' ? 'selected' : '' }}>
                        Training
                    </option>
                    <option value="resertifikasi" {{ $mode === 'resertifikasi' ? 'selected' : '' }}>
                        Resertifikasi
                    </option>
                    <option value="perpanjangan" {{ $mode === 'perpanjangan' ? 'selected' : '' }}>
                        Perpanjangan
                    </option>
                </select>

                {{-- SEARCH --}}
                <input type="text"
                       id="search"
                       placeholder="Cari training / job number..."
                       class="border rounded px-3 py-2 w-64"
                       value="{{ $search ?? '' }}">
            </div>
        </div>

        {{-- ================= EVENT AKTIF ================= --}}
        <h2 class="text-lg font-semibold mb-2">📌 Event Aktif</h2>

        <div id="table-container">
            @include('event_training.table-active', [
                'events' => $eventsActive,
                'mode'   => $mode
            ])
        </div>

        {{-- ================= EVENT PENDING ================= --}}
        @if($eventsPending)
            <h2 class="text-lg font-semibold mt-10 mb-2 text-yellow-700">
                ⏳ Event Pending (Menunggu ACC)
            </h2>

            @include('event_training.table-pending', [
                'events' => $eventsPending,
                'mode'   => $mode
            ])
        @endif
    </div>

    {{-- ================= LIVE SEARCH ================= --}}
    <script>
        const searchInput = document.getElementById('search');
        const modeSelect  = document.getElementById('mode');

        function fetchTable() {
            const keyword = searchInput.value;
            const mode    = modeSelect.value;

            fetch(`{{ route('event-training.index') }}?search=${keyword}&mode=${mode}`)
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('table-container').innerHTML =
                        doc.querySelector('#table-container').innerHTML;
                });
        }

        searchInput.addEventListener('input', fetchTable);
        modeSelect.addEventListener('change', fetchTable);
    </script>
</x-app-layout>
