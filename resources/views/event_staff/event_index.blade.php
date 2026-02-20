<x-app-layout>

    <div class="alkon-root py-10">
        <div class="max-w-7xl mx-auto space-y-6">


            {{-- HEADER --}}
            <div class="alkon-status">
                <div>
                    <h2 class="text-xl font-semibold">
                        Kelola Mentor & Staf Event
                    </h2>
                    <p class="text-sm text-gray-200">
                        Monitoring instruktur & training officer tiap training
                    </p>
                </div>
                <a href="{{ route('division.tools') }}"
                    class="alkon-btn-secondary text-xs bg-white/20 text-white hover:bg-white/30 border border-white/30">
                    ← Tools Divisi
                </a>
            </div>

            {{-- SEARCH PANEL --}}
            <div class="alkon-panel">
                <div class="alkon-panel-header">
                    Pencarian Training
                </div>

                <div class="alkon-panel-body flex items-center gap-4">

                    <input type="text" id="search" placeholder="Cari nama training..." class="alkon-input w-72"
                        value="{{ $search ?? '' }}">

                    <span class="text-sm text-gray-500">
                        Hasil akan muncul otomatis
                    </span>

                </div>
            </div>

            {{-- TABLE --}}
            <div id="table-container">
                @include('event_staff.table')
            </div>


        </div>
    </div>

    {{-- ================= LIVE SEARCH ================= --}}

    <script>
        const searchInput = document.getElementById('search');

        let timer;

        searchInput.addEventListener('input', function() {

            clearTimeout(timer);

            timer = setTimeout(() => {

                const keyword = this.value;

                fetch("{{ route('event-staff.events') }}?search=" + keyword)
                    .then(res => res.text())
                    .then(html => {

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const tableHtml =
                            doc.querySelector('#table-container').innerHTML;

                        document.getElementById('table-container').innerHTML =
                            tableHtml;

                    });

            }, 300); // debounce biar ga spam server
        });
    </script>

</x-app-layout>
