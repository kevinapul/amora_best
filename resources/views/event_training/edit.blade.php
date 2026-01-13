<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Edit Event
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto mt-10 bg-white p-8 shadow-lg rounded-xl">

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

        <form action="{{ route('event-training.update', $event) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ================= JENIS EVENT ================= --}}
            <div class="mb-4">
                <label class="font-medium">Jenis Event *</label>
                <select name="jenis_event" id="jenis_event"
                        class="w-full p-2 border rounded-lg mt-1" required>
                    <option value="">-- Pilih --</option>
                    <option value="training"
                        {{ old('jenis_event', $event->jenis_event) === 'training' ? 'selected' : '' }}>
                        Training
                    </option>
                    <option value="non_training"
                        {{ old('jenis_event', $event->jenis_event) === 'non_training' ? 'selected' : '' }}>
                        Non Training
                    </option>
                </select>
            </div>

            {{-- ================= PILIH TRAINING ================= --}}
            <div id="training_selector" style="display:none;">
                <div class="mb-4">
                    <label class="font-medium">
                        Pilih Training
                        <span id="training_hint" class="text-xs text-gray-500"></span>
                    </label>
                    <select name="training_id" class="w-full p-2 border rounded-lg mt-1">
                        <option value="">-- Pilih Training --</option>
                        @foreach ($trainings as $t)
                            <option value="{{ $t->id }}"
                                {{ old('training_id', $event->training_id) == $t->id ? 'selected' : '' }}>
                                {{ $t->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ================= DETAIL TRAINING ================= --}}
            <div id="training_detail" style="display:none;">
                <div class="mb-4">
                    <label class="font-medium">Tipe Training</label>
                    <select name="training_type" id="training_type"
                            class="w-full p-2 border rounded-lg mt-1">
                        <option value="">-- Pilih --</option>
                        <option value="reguler"
                            {{ old('training_type', $event->training_type) === 'reguler' ? 'selected' : '' }}>
                            Reguler
                        </option>
                        <option value="inhouse"
                            {{ old('training_type', $event->training_type) === 'inhouse' ? 'selected' : '' }}>
                            Inhouse
                        </option>
                    </select>
                </div>

                <div class="mb-4" id="harga_paket_wrapper" style="display:none;">
                    <label class="font-medium">Harga Paket (Inhouse)</label>
                    <input type="number" name="harga_paket"
                           value="{{ old('harga_paket', $event->harga_paket) }}"
                           class="w-full border rounded-lg p-2">
                </div>
            </div>

            {{-- ================= NON TRAINING ================= --}}
            <div id="non_training_section" style="display:none;">
                <div class="mb-4">
                    <label class="font-medium">Jenis Layanan</label>
                    <select name="non_training_type" id="non_training_type"
                            class="w-full p-2 border rounded-lg mt-1">
                        <option value="">-- Pilih --</option>
                        <option value="perpanjangan"
                            {{ old('non_training_type', $event->non_training_type) === 'perpanjangan' ? 'selected' : '' }}>
                            Perpanjangan Sertifikat
                        </option>
                        <option value="resertifikasi"
                            {{ old('non_training_type', $event->non_training_type) === 'resertifikasi' ? 'selected' : '' }}>
                            Re-Sertifikasi BNSP
                        </option>
                    </select>
                </div>
            </div>

            {{-- ================= FIELD UMUM ================= --}}
            <div id="common_fields">

                <div class="mb-4" id="job_number_field">
                    <label class="font-medium">Job Number</label>
                    <input type="text" name="job_number"
                           value="{{ old('job_number', $event->job_number) }}"
                           class="w-full border rounded-lg p-2">
                </div>

                <div class="mb-4">
                    <label class="font-medium">Tanggal Mulai</label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" name="start_day" placeholder="Hari"
                               value="{{ old('start_day', $start['day']) }}"
                               class="border p-2 rounded">
                        <input type="number" name="start_month" placeholder="Bulan"
                               value="{{ old('start_month', $start['month']) }}"
                               class="border p-2 rounded">
                        <input type="number" name="start_year" placeholder="Tahun"
                               value="{{ old('start_year', $start['year']) }}"
                               class="border p-2 rounded">
                    </div>
                </div>

                <div class="mb-4" id="tanggal_berakhir">
                    <label class="font-medium">Tanggal Berakhir</label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" name="end_day" placeholder="Hari"
                               value="{{ old('end_day', $end['day']) }}"
                               class="border p-2 rounded">
                        <input type="number" name="end_month" placeholder="Bulan"
                               value="{{ old('end_month', $end['month']) }}"
                               class="border p-2 rounded">
                        <input type="number" name="end_year" placeholder="Tahun"
                               value="{{ old('end_year', $end['year']) }}"
                               class="border p-2 rounded">
                    </div>
                </div>

                <div class="mb-4" id="tempat_field">
                    <label class="font-medium">Tempat</label>
                    <input type="text" name="tempat"
                           value="{{ old('tempat', $event->tempat) }}"
                           class="w-full border rounded-lg p-2">
                </div>

                <div class="mb-4">
                    <label class="font-medium">Jenis Sertifikasi</label>
                    <select name="jenis_sertifikasi" id="jenis_sertifikasi"
                            class="w-full border rounded-lg p-2">
                        <option value="">-- Pilih --</option>
                        @foreach (['Kementrian','Bnsp','Alkon Best Mandiri'] as $j)
                            <option value="{{ $j }}"
                                {{ old('jenis_sertifikasi', $event->jenis_sertifikasi) === $j ? 'selected' : '' }}>
                                {{ $j }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4" id="kemitraan_field">
                    <label class="font-medium">Kemitraan</label>
                    <input type="text" name="sertifikasi"
                           value="{{ old('sertifikasi', $event->sertifikasi) }}"
                           class="w-full border rounded-lg p-2">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <a href="{{ route('event-training.index') }}"
                   class="px-4 py-2 bg-gray-500 text-white rounded-lg">
                    Kembali
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
                    Update
                </button>
            </div>
        </form>
    </div>

    {{-- ================= SCRIPT ================= --}}
    <script>
        const jenisEvent = document.getElementById('jenis_event');
        const nonTrainingType = document.getElementById('non_training_type');
        const trainingType = document.getElementById('training_type');

        const trainingSelector = document.getElementById('training_selector');
        const trainingDetail = document.getElementById('training_detail');
        const nonTrainingSection = document.getElementById('non_training_section');
        const hargaWrapper = document.getElementById('harga_paket_wrapper');
        const tanggalBerakhir = document.getElementById('tanggal_berakhir');
        const tempatField = document.getElementById('tempat_field');
        const jobNumberField = document.getElementById('job_number_field');
        const kemitraanField = document.getElementById('kemitraan_field');
        const jenisSertifikasi = document.getElementById('jenis_sertifikasi');
        const trainingHint = document.getElementById('training_hint');

        function toggle(section, show) {
            section.style.display = show ? 'block' : 'none';
            section.querySelectorAll('input, select').forEach(el => {
                el.disabled = !show;
            });
        }

        function resetAll() {
            toggle(trainingSelector, false);
            toggle(trainingDetail, false);
            toggle(nonTrainingSection, false);
            toggle(hargaWrapper, false);

            toggle(tanggalBerakhir, true);
            toggle(tempatField, true);
            toggle(jobNumberField, true);
            toggle(kemitraanField, true);

            trainingHint.innerText = '';
        }

        function updateForm() {
            resetAll();

            if (jenisEvent.value === 'training') {
                toggle(trainingSelector, true);
                toggle(trainingDetail, true);

                if (trainingType.value === 'inhouse') {
                    toggle(hargaWrapper, true);
                }
            }

            if (jenisEvent.value === 'non_training') {
                toggle(nonTrainingSection, true);

                if (nonTrainingType.value === 'perpanjangan') {
                    toggle(tanggalBerakhir, false);
                    toggle(tempatField, false);
                    toggle(jobNumberField, false);
                    toggle(kemitraanField, false);
                }

                if (nonTrainingType.value === 'resertifikasi') {
                    toggle(trainingSelector, true);
                    jenisSertifikasi.value = 'Bnsp';
                    trainingHint.innerText = '(untuk re-sertifikasi)';
                }
            }
        }

        jenisEvent.addEventListener('change', updateForm);
        nonTrainingType?.addEventListener('change', updateForm);
        trainingType?.addEventListener('change', updateForm);

        document.addEventListener('DOMContentLoaded', updateForm);
    </script>
</x-app-layout>
