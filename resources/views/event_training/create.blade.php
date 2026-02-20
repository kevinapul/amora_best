<x-app-layout>
    <div class="alkon-root py-10">
        <div class="max-w-4xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div class="alkon-status">
                <div>
                    <h2 class="text-xl font-semibold">
                        Tambah Event Training
                    </h2>
                    <p class="text-sm text-gray-200">
                        Buat group training & event turunan
                    </p>
                </div>
            </div>

            {{-- ERROR --}}
            @if ($errors->any())
                <div class="alkon-panel border-red-300 bg-red-50">
                    <div class="alkon-panel-body">
                        <ul class="text-red-600 text-sm list-disc ml-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- FORM --}}
            <form action="{{ route('event-training.store') }}" method="POST">
                @csrf

                <div class="alkon-panel">
                    <div class="alkon-panel-header">
                        Informasi Training
                    </div>

                    <div class="alkon-panel-body space-y-5">

                        {{-- MASTER TRAINING --}}
                        <div>
                            <label class="text-sm font-semibold">Master Training *</label>
                            <select id="master_training" name="master_training_id" class="alkon-input mt-1" required>
                                <option value="">-- Pilih Master Training --</option>
                                @foreach ($masters as $m)
                                    <option value="{{ $m->id }}" data-trainings='@json($m->trainings)'>
                                        {{ $m->nama_training }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- GRID --}}
                        <div class="grid grid-cols-2 gap-5">

                            <div>
                                <label class="text-sm font-semibold">Job Number</label>
                                <input type="text" name="job_number" class="alkon-input">
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Tempat</label>
                                <input type="text" name="tempat" class="alkon-input">
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Jenis Sertifikasi</label>
                                <select name="jenis_sertifikasi" class="alkon-input">
                                    <option value="">-- Pilih --</option>
                                    <option value="KEMENTERIAN">Kementerian</option>
                                    <option value="BNSP">BNSP</option>
                                    <option value="INTERNAL">Alkon Best Mandiri</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Kemitraan</label>
                                <input type="text" name="sertifikasi" class="alkon-input">
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Tipe Training *</label>
                                <select name="training_type" id="training_type" class="alkon-input" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="reguler">Reguler</option>
                                    <option value="inhouse">Inhouse</option>
                                </select>
                            </div>

                        </div>

                        {{-- HARGA INHOUSE --}}
                        <div class="hidden" id="harga-paket-wrapper">
                            <label class="text-sm font-semibold">
                                Harga Paket Inhouse
                            </label>

                            <div class="flex border rounded-lg overflow-hidden mt-1">
                                <span class="px-4 bg-gray-100 flex items-center font-semibold">
                                    Rp
                                </span>
                                <input type="text" id="harga_paket_display" class="flex-1 p-2 outline-none"
                                    placeholder="0">
                            </div>

                            <input type="hidden" name="harga_paket" id="harga_paket_value">

                            <p class="text-xs text-gray-500 mt-1">
                                Total kontrak training inhouse
                            </p>
                        </div>

                        {{-- BILLING COMPANY --}}
                        <div class="hidden" id="billing-company-wrapper">
                            <label class="text-sm font-semibold">
                                Perusahaan Penagihan
                            </label>

                            <select id="billing_company_select" name="billing_company_id" class="alkon-input mt-1">
                                <option value="">-- Pilih Perusahaan --</option>

                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">
                                        {{ $company->name }}
                                    </option>
                                @endforeach

                                <option value="manual">+ Tulis Perusahaan Baru</option>
                            </select>

                            <div id="manual-company-wrapper" class="hidden mt-2">
                                <input type="text" name="manual_company" placeholder="Tulis nama perusahaan..."
                                    class="alkon-input">
                            </div>

                            <p class="text-xs text-gray-500 mt-1">
                                Invoice inhouse hanya ke perusahaan ini
                            </p>
                        </div>

                    </div>
                </div>

                {{-- EVENT ANAK --}}
                <div id="child-events" class="space-y-4"></div>

                {{-- ACTION --}}
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('event-training.index') }}" class="alkon-btn-secondary">
                        ← Kembali
                    </a>

                    <button class="alkon-btn-primary">
                        Simpan Event
                    </button>
                </div>

            </form>


        </div>
    </div>


    <script>
        const masterSelect = document.getElementById('master_training');
        const container = document.getElementById('child-events');

        const days = [...Array(31)].map((_, i) => i + 1);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const years = [...Array(5)].map((_, i) => new Date().getFullYear() + i);

        function options(arr) {
            return arr.map(v =>
                `<option value="${typeof v==='number'?v:v.toUpperCase()}">${v}</option>`
            ).join('');
        }

        masterSelect.addEventListener('change', () => {
            container.innerHTML = '';
            if (!masterSelect.value) return;

            const trainings = JSON.parse(
                masterSelect.selectedOptions[0].dataset.trainings
            );

            trainings.forEach((t, i) => {
                container.insertAdjacentHTML('beforeend', `
        <div class="alkon-panel">
        <div class="alkon-panel-header">
            ${i+1}. ${t.name}
        </div>

        <div class="alkon-panel-body space-y-4">

            <input type="hidden"
                name="events[${i}][training_id]"
                value="${t.id}">

            <div>
                <label class="text-sm font-semibold">Jenis Event *</label>
                <select name="events[${i}][jenis_event]"
                    class="alkon-input" required>
                    <option value="">-- Pilih --</option>
                    <option value="training">Training</option>
                    <option value="non_training">Non Training</option>
                </select>
            </div>

            <div class="hidden" data-non-training-wrapper>
                <label class="text-sm font-semibold">Jenis Non Training</label>
                <select name="events[${i}][non_training_type]"
                    class="alkon-input" disabled>
                    <option value="">-- Pilih --</option>
                    <option value="perpanjangan">Perpanjangan</option>
                    <option value="resertifikasi">Resertifikasi</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold">Tanggal Mulai</label>
                    <div class="grid grid-cols-3 gap-2">
                        <select name="events[${i}][start_day]" class="alkon-input">${options(days)}</select>
                        <select name="events[${i}][start_month]" class="alkon-input">${options(months)}</select>
                        <select name="events[${i}][start_year]" class="alkon-input">${options(years)}</select>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold">Tanggal Berakhir</label>
                    <div class="grid grid-cols-3 gap-2">
                        <select name="events[${i}][end_day]" class="alkon-input">${options(days)}</select>
                        <select name="events[${i}][end_month]" class="alkon-input">${options(months)}</select>
                        <select name="events[${i}][end_year]" class="alkon-input">${options(years)}</select>
                    </div>
                </div>
            </div>

        </div>
        </div>
        `);
            });
        });
    </script>

    {{-- INHOUSE SCRIPT --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trainingType = document.getElementById('training_type');
            const hargaWrapper = document.getElementById('harga-paket-wrapper');
            const billingWrapper = document.getElementById('billing-company-wrapper');

            const displayInput = document.getElementById('harga_paket_display');
            const valueInput = document.getElementById('harga_paket_value');

            function toggleInhouse() {
                if (trainingType.value === 'inhouse') {
                    hargaWrapper.classList.remove('hidden');
                    billingWrapper.classList.remove('hidden');
                } else {
                    hargaWrapper.classList.add('hidden');
                    billingWrapper.classList.add('hidden');
                    displayInput.value = '';
                    valueInput.value = '';
                }
            }

            trainingType.addEventListener('change', toggleInhouse);
            toggleInhouse();

            function formatRupiah(value) {
                return value.replace(/\D/g, '')
                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            displayInput.addEventListener('input', function() {
                const raw = this.value.replace(/\D/g, '');
                this.value = formatRupiah(raw);
                valueInput.value = raw;
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('billing_company_select');
            const manualWrapper = document.getElementById('manual-company-wrapper');
            if (!select) return;

            function toggleManual() {
                if (select.value === 'manual') {
                    manualWrapper.classList.remove('hidden');
                } else {
                    manualWrapper.classList.add('hidden');
                }
            }
            select.addEventListener('change', toggleManual);
        });
    </script>

</x-app-layout>
