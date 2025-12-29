<x-app-layout>
    <div class="p-8 max-w-5xl mx-auto">

        <h1 class="text-2xl font-bold mb-6">Kelola Mentor / Staf Event</h1>

        <div class="flex items-center justify-between mb-4">
            
            {{-- Search --}}
            <input type="text" id="search" placeholder="Cari nama training..."
                   class="border rounded px-3 py-2 w-60"
                   value="{{ $search ?? '' }}">
        </div>

        {{-- Table container --}}
        <div id="table-container">
            @include('event_staff.table')
        </div>

    </div>

    {{-- Live search script --}}
    <script>
        const searchInput = document.getElementById('search');

        searchInput.addEventListener('input', function () {
            const keyword = this.value;

            fetch("{{ route('event-staff.events') }}?search=" + keyword)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const tableHtml = doc.querySelector('#table-container').innerHTML;
                    document.getElementById('table-container').innerHTML = tableHtml;
                });
        });
    </script>
</x-app-layout>
