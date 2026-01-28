<x-app-layout>

    <div class="alkon-root">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- ================= HEADER ================= -->
            <div class="alkon-panel mb-6">
                <div class="alkon-panel-body flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">
                            📅 Event Training
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            Kelola event training aktif dan pending.
                        </p>
                    </div>

                    <a href="{{ route('event-training.create') }}" class="alkon-btn-primary">
                        + Tambah Event
                    </a>
                </div>
            </div>

            <!-- ================= TAB + SEARCH ================= -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">

                <!-- TABS -->
                <div class="flex gap-2">
                    <button id="tab-active" class="tab-btn-active px-4 py-2 rounded-md border text-sm font-medium">
                        📌 Event Aktif
                    </button>

                    <button id="tab-pending" class="tab-btn-inactive px-4 py-2 rounded-md border text-sm font-medium">
                        ⏳ Event Pending
                    </button>
                </div>

                <!-- SEARCH -->
                <input type="text" id="search" placeholder="Cari master / job number..."
                    class="w-full sm:w-72 px-3 py-2 rounded-lg border border-gray-300
                           focus:outline-none focus:ring-2 focus:ring-[var(--alkon-green)]"
                    value="{{ $search ?? '' }}">
            </div>

            <!-- ================= CONTENT ================= -->

            <!-- EVENT AKTIF -->
            <div id="content-active" class="alkon-panel">
                <div class="alkon-panel-body p-0">
                    <div id="table-active">
                        @include('event_training.table-active', [
                            'groups' => $groupsActive,
                        ])
                    </div>
                </div>
            </div>

            <!-- EVENT PENDING -->
            @if ($groupsPending)
                <div id="content-pending" class="alkon-panel hidden mt-4">
                    <div class="alkon-panel-body p-0">
                        <div id="table-pending">
                            @include('event_training.table-pending', [
                                'groups' => $groupsPending,
                            ])
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- ================= SCRIPT (SEARCH + TAB) ================= -->
    <script>
        // SEARCH (ASLI, TIDAK DIUBAH)
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

        // TAB SWITCH
        const tabActive = document.getElementById('tab-active');
        const tabPending = document.getElementById('tab-pending');
        const contentActive = document.getElementById('content-active');
        const contentPending = document.getElementById('content-pending');

        tabActive.addEventListener('click', () => {
            tabActive.classList.add('tab-btn-active');
            tabActive.classList.remove('tab-btn-inactive');

            tabPending.classList.add('tab-btn-inactive');
            tabPending.classList.remove('tab-btn-active');

            contentActive.classList.remove('hidden');
            contentPending?.classList.add('hidden');
        });

        tabPending.addEventListener('click', () => {
            tabPending.classList.add('tab-btn-active');
            tabPending.classList.remove('tab-btn-inactive');

            tabActive.classList.add('tab-btn-inactive');
            tabActive.classList.remove('tab-btn-active');

            contentPending?.classList.remove('hidden');
            contentActive.classList.add('hidden');
        });
    </script>

    <!-- ================= TAB STYLE ================= -->
    <style>
        .tab-btn-active {
            background: white;
            border: 2px solid var(--alkon-green);
            color: var(--alkon-green);
            font-weight: 600;
        }

        .tab-btn-inactive {
            background: #f9fafb;
            border: 2px solid #d1d5db;
            color: #374151;
        }

        .tab-btn-inactive:hover {
            border-color: var(--alkon-green);
            color: var(--alkon-green);
        }
    </style>

</x-app-layout>
