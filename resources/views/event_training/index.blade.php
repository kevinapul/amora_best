<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">

        <h1 class="text-2xl font-bold mb-6">Event Training</h1>

        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('event-training.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">
                + Tambah Event
            </a>

            <input type="text" id="search" placeholder="Cari master / job number..."
                class="border rounded px-3 py-2 w-64" value="{{ $search ?? '' }}">
        </div>

        {{-- ================= AKTIF ================= --}}
        <h2 class="text-lg font-semibold mb-2">📌 Event Aktif</h2>

        <div id="table-active">
            @include('event_training.table-active', [
                'groups' => $groupsActive,
            ])
        </div>

        {{-- ================= PENDING ================= --}}
        @if ($groupsPending)
            <h2 class="text-lg font-semibold mt-10 mb-2 text-yellow-700">
                ⏳ Event Pending (Menunggu ACC)
            </h2>

            <div id="table-pending">
                @include('event_training.table-pending', [
                    'groups' => $groupsPending,
                ])
            </div>
        @endif

    </div>

    <script>
        const search = document.getElementById('search');
        let t = null;

        search.addEventListener('input', () => {
            clearTimeout(t);
            t = setTimeout(() => {
                fetch(`{{ route('event-training.index') }}?search=${search.value}`)
                    .then(r => r.text())
                    .then(html => {
                        const d = new DOMParser().parseFromString(html, 'text/html');
                        document.getElementById('table-active').innerHTML =
                            d.querySelector('#table-active').innerHTML;

                        if (d.querySelector('#table-pending')) {
                            document.getElementById('table-pending').innerHTML =
                                d.querySelector('#table-pending').innerHTML;
                        }
                    });
            }, 300);
        });
    </script>
</x-app-layout>
