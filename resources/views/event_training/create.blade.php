<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Tambah Event
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto mt-10 bg-white p-8 shadow-lg rounded-xl">

        {{-- ERROR VALIDATION --}}
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

            {{-- ================= JENIS EVENT ================= --}}
            <div class="mb-4">
                <label class="font-medium">Jenis Event <span class="text-red-500">*</span></label>
                <select name="jenis_event" id="jenis_event" class="w-full p-2 border rounded-lg mt-1" required>
                    <option value="">-- Pilih Jenis Event --</option>
                    <option value="training" {{ old('jenis_event') === 'training' ? 'selected' : '' }}>Training</option>
                    <option value="non_training" {{ old('jenis_event') === 'non_training' ? 'selected' : '' }}>Non
                        Training</option>
                </select>
            </div>

            {{-- ================= TRAINING ================= --}}
            <div id="training_section" style="display:none;">
                <div class="mb-4">
                    <label class="font-medium">Pilih Training</label>
                    <select name="training_id" class="w-full p-2 border rounded-lg mt-1">
                        <option value="">-- Pilih Training --</option>
                        @foreach ($trainings as $t)
                            <option value="{{ $t->id }}" {{ old('training_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="font-medium">Tipe Training</label>
                    <select name="training_type" id="training_type" class="w-full p-2 border rounded-lg mt-1">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="reguler" {{ old('training_type') === 'reguler' ? 'selected' : '' }}>Reguler</option>
                        <option value="inhouse" {{ old('training_type') === 'inhouse' ? 'selected' : '' }}>Inhouse</option>
                    </select>
                </div>

                <div class="mb-4" id="harga_paket_wrapper" style="display:none;">
                    <label class="font-medium">Harga Paket (Inhouse)</label>
                    <input type="number" name="harga_paket" value="{{ old('harga_paket') }}" class="w-full border rounded-lg p-2">
                </div>
            </div>

            {{-- ================= NON TRAINING ================= --}}
            <div id="non_training_section" style="display:none;">
                <div class="mb-4">
                    <label class="font-medium">Jenis Layanan</label>
                    <select name="non_training_type" id="non_training_type" class="w-full p-2 border rounded-lg mt-1">
                        <option value="">-- Pilih Layanan --</option>
                        <option value="perpanjangan" {{ old('non_training_type') === 'perpanjangan' ? 'selected' : '' }}>Perpanjangan Sertifikat</option>
                        <option value="resertifikasi" {{ old('non_training_type') === 'resertifikasi' ? 'selected' : '' }}>Re-Sertifikasi BNSP</option>
                    </select>
                </div>
            </div>

            {{-- ================= FIELD UMUM ================= --}}
            <div id="common_fields">
                <div class="mb-4">
                    <label class="font-medium">Job Number</label>
                    <input type="text" name="job_number" value="{{ old('job_number') }}" class="w-full border rounded-lg p-2">
                </div>

                {{-- TANGGAL MULAI --}}
                <div class="mb-4">
                    <label class="font-medium">Tanggal Mulai</label>
                    <div class="grid grid-cols-3 gap-2 mt-1">
                        <select name="start_day" class="border rounded-lg p-2" required>
                            <option value="">Hari</option>
                            @for ($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ old('start_day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>

                        <select name="start_month" class="border rounded-lg p-2" required>
                            <option value="">Bulan</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('start_month') == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>

                        <select name="start_year" class="border rounded-lg p-2" required>
                            <option value="">Tahun</option>
                            @for ($y = date('Y'); $y <= date('Y') + 3; $y++)
                                <option value="{{ $y }}" {{ old('start_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- TANGGAL BERAKHIR --}}
                <div class="mb-4">
                    <label class="font-medium">Tanggal Berakhir</label>
                    <div class="grid grid-cols-3 gap-2 mt-1">
                        <select name="end_day" class="border rounded-lg p-2" required>
                            <option value="">Hari</option>
                            @for ($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ old('end_day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>

                        <select name="end_month" class="border rounded-lg p-2" required>
                            <option value="">Bulan</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('end_month') == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>

                        <select name="end_year" class="border rounded-lg p-2" required>
                            <option value="">Tahun</option>
                            @for ($y = date('Y'); $y <= date('Y') + 3; $y++)
                                <option value="{{ $y }}" {{ old('end_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="font-medium">Tempat</label>
                    <input type="text" name="tempat" value="{{ old('tempat') }}" class="w-full border rounded-lg p-2">
                </div>

                <div class="mb-4">
                    <label class="font-medium">Jenis Sertifikasi</label>
                    <select name="jenis_sertifikasi" id="jenis_sertifikasi" class="w-full border rounded-lg p-2">
                        <option value="">-- Pilih Jenis Sertifikasi --</option>
                        <option value="Kementrian" {{ old('jenis_sertifikasi') === 'Kementrian' ? 'selected' : '' }}>Kementrian</option>
                        <option value="Bnsp" {{ old('jenis_sertifikasi') === 'Bnsp' ? 'selected' : '' }}>BNSP</option>
                        <option value="Alkon Best Mandiri" {{ old('jenis_sertifikasi') === 'Alkon Best Mandiri' ? 'selected' : '' }}>Alkon Best Mandiri</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="font-medium">Kemitraan</label>
                    <input type="text" name="sertifikasi" value="{{ old('sertifikasi') }}" class="w-full border rounded-lg p-2">
                </div>
            </div>

            {{-- ================= BUTTON ================= --}}
            <div class="flex justify-end gap-2 mt-6">
                <a href="{{ route('event-training.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg">Kembali</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>

    {{-- ================= SCRIPT ================= --}}
    <script>
        const jenisEvent = document.getElementById('jenis_event');
        const trainingSection = document.getElementById('training_section');
        const nonTrainingSection = document.getElementById('non_training_section');
        const trainingType = document.getElementById('training_type');
        const hargaWrapper = document.getElementById('harga_paket_wrapper');
        const nonTrainingType = document.getElementById('non_training_type');
        const commonFields = document.getElementById('common_fields');
        const jenisSertifikasi = document.getElementById('jenis_sertifikasi');
        const kemitraanField = document.querySelector('input[name="sertifikasi"]').closest('.mb-4');

        function updateFormVisibility() {
            // reset semua dulu
            trainingSection.style.display = 'none';
            nonTrainingSection.style.display = 'none';
            hargaWrapper.style.display = 'none';
            commonFields.style.display = 'block'; // default show

            // ambil elemen field
            const tanggalMulai = document.querySelector('[name="start_day"]').closest('.mb-4');
            const tanggalBerakhir = document.querySelector('[name="end_day"]').closest('.mb-4');
            const tempatField = document.querySelector('input[name="tempat"]').closest('.mb-4');
            const jobNumberField = document.querySelector('input[name="job_number"]').closest('.mb-4');
            const jenisSertifikasiField = document.querySelector('#jenis_sertifikasi').closest('.mb-4');

            // default show semua
            tanggalMulai.style.display = 'block';
            tanggalBerakhir.style.display = 'block';
            tempatField.style.display = 'block';
            jobNumberField.style.display = 'block';
            jenisSertifikasiField.style.display = 'block';
            kemitraanField.style.display = 'block';

            if (jenisEvent.value === 'training') {
                trainingSection.style.display = 'block';
                if (trainingType.value === 'inhouse') {
                    hargaWrapper.style.display = 'block';
                }
            } else if (jenisEvent.value === 'non_training') {
                nonTrainingSection.style.display = 'block';

                if (nonTrainingType.value === 'perpanjangan') {
                    // cuma tampil tanggal mulai + jenis sertifikasi
                    tanggalBerakhir.style.display = 'none';
                    tempatField.style.display = 'none';
                    jobNumberField.style.display = 'none';
                    kemitraanField.style.display = 'none';
                }

                if (nonTrainingType.value === 'resertifikasi') {
                    jenisSertifikasi.value = 'Bnsp';
                    tanggalBerakhir.style.display = 'block';
                    tempatField.style.display = 'block';
                    jobNumberField.style.display = 'block';
                    kemitraanField.style.display = 'block';
                }
            }
        }

        jenisEvent.addEventListener('change', updateFormVisibility);
        trainingType?.addEventListener('change', updateFormVisibility);
        nonTrainingType?.addEventListener('change', updateFormVisibility);

        // initialize
        updateFormVisibility();
    </script>
</x-app-layout>
