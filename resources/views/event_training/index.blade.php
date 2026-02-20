<x-app-layout>

    <div class="alkon-root py-10">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div class="alkon-status flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold">
                        Event Training Management
                    </h2>
                    <p class="text-sm text-gray-200">
                        Monitoring event aktif & pending
                    </p>
                </div>

                <a href="{{ route('event-training.create') }}" class="alkon-btn-primary">
                    + Tambah Event
                </a>
            </div>


            {{-- FILTER BULAN + SEARCH --}}
            <div class="alkon-panel">
                <div class="alkon-panel-body flex flex-col md:flex-row md:items-center gap-3 md:justify-between">

                    <form method="GET" class="flex gap-3">

                        {{-- FILTER BULAN --}}
                        <select name="month" onchange="this.form.submit()" class="alkon-input w-56">

                            @for ($m = 1; $m <= 12; $m++)
                                @php
                                    $val = date('Y') . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                                    $label = \Carbon\Carbon::create()->month($m)->format('F');
                                @endphp

                                <option value="{{ $val }}" {{ $month == $val ? 'selected' : '' }}>
                                    {{ $label }} {{ date('Y') }}
                                </option>
                            @endfor
                        </select>

                        {{-- SEARCH --}}
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Cari job / training..." class="alkon-input w-64">

                        <button class="alkon-btn-secondary text-xs">
                            Filter
                        </button>
                    </form>

                    <div class="text-xs text-gray-500">
                        Menampilkan event bulan terpilih
                    </div>

                </div>
            </div>


            {{-- TAB --}}
            <div class="flex gap-2">
                <button id="tab-active" class="tab-btn-active px-4 py-2 rounded-md text-sm">
                    📌 Event Aktif
                </button>

                @if ($groupsPending)
                    <button id="tab-pending" class="tab-btn-inactive px-4 py-2 rounded-md text-sm">
                        ⏳ Pending Approval
                    </button>
                @endif
            </div>


            {{-- ACTIVE --}}
            <div id="content-active" class="alkon-panel">
                <div class="alkon-panel-body p-0">
                    @include('event_training.table-active', ['groups' => $groupsActive])
                </div>
            </div>


            {{-- PENDING --}}
            @if ($groupsPending)
                <div id="content-pending" class="alkon-panel hidden">
                    <div class="alkon-panel-body p-0">
                        @include('event_training.table-pending', ['groups' => $groupsPending])
                    </div>
                </div>
            @endif


        </div>
    </div>


    {{-- TAB SCRIPT --}}
    <script>
        const tabActive = document.getElementById('tab-active');
        const tabPending = document.getElementById('tab-pending');
        const contentActive = document.getElementById('content-active');
        const contentPending = document.getElementById('content-pending');

        tabActive?.addEventListener('click', () => {
            tabActive.classList.add('tab-btn-active');
            tabActive.classList.remove('tab-btn-inactive');

            tabPending?.classList.remove('tab-btn-active');
            tabPending?.classList.add('tab-btn-inactive');

            contentActive.classList.remove('hidden');
            contentPending?.classList.add('hidden');
        });

        tabPending?.addEventListener('click', () => {
            tabPending.classList.add('tab-btn-active');
            tabPending.classList.remove('tab-btn-inactive');

            tabActive.classList.remove('tab-btn-active');
            tabActive.classList.add('tab-btn-inactive');

            contentPending.classList.remove('hidden');
            contentActive.classList.add('hidden');
        });
    </script>


    <style>
        .tab-btn-active {
            background: white;
            border: 2px solid #0f3d2e;
            color: #0f3d2e;
            font-weight: 600;
        }

        .tab-btn-inactive {
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            color: #374151;
        }
    </style>

</x-app-layout>
