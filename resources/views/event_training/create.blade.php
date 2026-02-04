<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Tambah Event</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto mt-10 bg-white p-8 shadow-lg rounded-xl">

        {{-- ERROR --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-400 rounded">
                <ul class="text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('event-training.store') }}" method="POST">
            @csrf

            {{-- ================= MASTER TRAINING ================= --}}
            <div class="mb-6">
                <label class="font-medium">Master Training *</label>
                <select id="master_training" name="master_training_id" class="w-full p-2 border rounded-lg mt-1"
                    required>
                    <option value="">-- Pilih Master Training --</option>
                    @foreach ($masters as $m)
                        <option value="{{ $m->id }}" data-trainings='@json($m->trainings)'>
                            {{ $m->nama_training }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ================= GROUP FIELDS ================= --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="font-medium">Job Number</label>
                    <input type="text" name="job_number" class="w-full border rounded-lg p-2">
                </div>

                <div>
                    <label class="font-medium">Tempat</label>
                    <input type="text" name="tempat" class="w-full border rounded-lg p-2">
                </div>

                <div>
                    <label class="font-medium">Jenis Sertifikasi</label>
                    <select name="jenis_sertifikasi" class="w-full border rounded-lg p-2">
                        <option value="">-- Pilih --</option>
                        <option value="KEMENTERIAN">Kementerian</option>
                        <option value="BNSP">BNSP</option>
                        <option value="INTERNAL">Alkon Best Mandiri</option>
                    </select>
                </div>

                <div>
                    <label class="font-medium">Kemitraan</label>
                    <input type="text" name="sertifikasi" class="w-full border rounded-lg p-2">
                </div>

                <div class="mb-6">
                    <label class="font-medium">Tipe Training *</label>
                    <select name="training_type" id="training_type" class="w-full border rounded-lg p-2" required>
                        <option value="">-- Pilih --</option>
                        <option value="reguler">Reguler</option>
                        <option value="inhouse">Inhouse</option>
                    </select>
                </div>

                <div class="mb-6 hidden" id="harga-paket-wrapper">
                    <label class="font-medium">Harga Paket (Inhouse)</label>

                    <div class="flex items-center border rounded-lg overflow-hidden">
                        <span class="px-3 bg-gray-100 text-gray-600 font-medium">Rp</span>
                        <input type="text" id="harga_paket_display" class="flex-1 p-2 outline-none" placeholder="0">
                    </div>

                    <input type="hidden" name="harga_paket" id="harga_paket_value">

                    <p class="text-sm text-gray-500 mt-1">
                        Harga total kontrak inhouse
                    </p>
                </div>

                <div class="mb-6 hidden" id="billing-company-wrapper">
                    <label class="font-medium">Perusahaan Induk (Penagihan)</label>

                    <select name="billing_company_id" class="w-full border rounded-lg p-2 mt-1">
                        <option value="">-- Pilih Perusahaan --</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>

                    <p class="text-sm text-gray-500 mt-1">
                        Invoice INHOUSE hanya akan ditagihkan ke perusahaan ini
                    </p>
                </div>


            </div>

            <hr class="my-6">

            {{-- ================= EVENT ANAK ================= --}}
            <div id="child-events"></div>

            <div class="flex justify-end gap-2 mt-8">
                <a href="{{ route('event-training.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg">
                    Kembali
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    {{-- ================= SCRIPT GENERATE ANAK ================= --}}
    <script>
        const masterSelect = document.getElementById('master_training');
        const container = document.getElementById('child-events');

        const days = [...Array(31)].map((_, i) => i + 1);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const years = [...Array(5)].map((_, i) => new Date().getFullYear() + i);

        function options(arr) {
            return arr.map(v =>
                `<option value="${typeof v === 'number' ? v : v.toUpperCase()}">${v}</option>`
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
                    <div data-event-card class="border rounded-xl p-5 mt-6 bg-gray-50">

                        <h3 class="font-semibold mb-4">
                            ${i + 1}. ${t.name}
                        </h3>

                        <input type="hidden"
                               name="events[${i}][training_id]"
                               value="${t.id}">

                        {{-- JENIS EVENT --}}
                        <div class="mb-3">
                            <label class="font-medium">Jenis Event *</label>
                            <select name="events[${i}][jenis_event]"
                                    class="w-full border p-2 rounded"
                                    required>
                                <option value="">-- Pilih --</option>
                                <option value="training">Training</option>
                                <option value="non_training">Non Training</option>
                            </select>
                        </div>

                        {{-- NON TRAINING TYPE --}}
                        <div class="mb-3 hidden" data-non-training-wrapper>
                            <label class="font-medium">Jenis Non Training</label>
                            <select name="events[${i}][non_training_type]"
                                    class="w-full border p-2 rounded"
                                    disabled>
                                <option value="">-- Pilih --</option>
                                <option value="perpanjangan">Perpanjangan</option>
                                <option value="resertifikasi">Resertifikasi</option>
                            </select>
                        </div>

                        {{-- TANGGAL --}}
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="font-medium">Tanggal Mulai</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <select name="events[${i}][start_day]" class="border p-2 rounded">${options(days)}</select>
                                    <select name="events[${i}][start_month]" class="border p-2 rounded">${options(months)}</select>
                                    <select name="events[${i}][start_year]" class="border p-2 rounded">${options(years)}</select>
                                </div>
                            </div>

                            <div>
                                <label class="font-medium">Tanggal Berakhir</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <select name="events[${i}][end_day]" class="border p-2 rounded">${options(days)}</select>
                                    <select name="events[${i}][end_month]" class="border p-2 rounded">${options(months)}</select>
                                    <select name="events[${i}][end_year]" class="border p-2 rounded">${options(years)}</select>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });
        });
    </script>

    {{-- ================= SCRIPT TOGGLE NON TRAINING (FINAL FIX) ================= --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const trainingType   = document.getElementById('training_type');
    const hargaWrapper   = document.getElementById('harga-paket-wrapper');
    const billingWrapper = document.getElementById('billing-company-wrapper');

    const displayInput = document.getElementById('harga_paket_display');
    const valueInput   = document.getElementById('harga_paket_value');

    function toggleInhouse() {
        if (trainingType.value === 'inhouse') {
            hargaWrapper.classList.remove('hidden');
            billingWrapper.classList.remove('hidden');
        } else {
            hargaWrapper.classList.add('hidden');
            billingWrapper.classList.add('hidden');
            displayInput.value = '';
            valueInput.value   = '';
        }
    }

    trainingType.addEventListener('change', toggleInhouse);

    // kalau halaman reload & value sudah ada
    toggleInhouse();

    function formatRupiah(value) {
        return value.replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    displayInput.addEventListener('input', function () {
        const raw = this.value.replace(/\D/g, '');
        this.value = formatRupiah(raw);
        valueInput.value = raw;
    });
});
</script>

</x-app-layout>
