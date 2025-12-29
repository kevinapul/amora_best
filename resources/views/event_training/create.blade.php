<x-app-layout>

    {{-- OPTIONAL: Header --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Tambah Event Training
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto mt-10 bg-white p-8 shadow-lg rounded-xl">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 text-center">
            Tambah Event Training
        </h1>

        <form action="{{ route('event-training.store') }}" method="POST">
            @csrf

            {{-- PILIH TRAINING --}}
            <div class="mb-4">
                <label class="font-medium">Pilih Training</label>
                <select name="training_id" class="w-full p-2 border rounded-lg mt-1" required>
                    <option value="">-- Pilih Training --</option>
                    @foreach ($trainings as $t)
                        <option value="{{ $t->id }}">{{ $t->code }}</option>
                    @endforeach
                </select>
            </div>

            {{-- JENIS EVENT --}}
            <div class="mb-4">
                <label class="font-medium">Jenis Event</label>
                <select name="jenis_event" id="jenis_event" class="w-full p-2 border rounded-lg mt-1" required>
                    <option value="reguler">Reguler</option>
                    <option value="inhouse">Inhouse</option>
                </select>
            </div>

            {{-- HARGA PAKET --}}
            <div class="mb-4" id="harga_paket_wrapper" style="display: none;">
                <label class="font-medium">Harga Paket (Inhouse)</label>
                <input type="number" name="harga_paket" class="w-full border rounded-lg p-2"
                    placeholder="Masukkan total harga paket">
            </div>

            {{-- JOB NUMBER --}}
            <div class="mb-4">
                <label class="font-medium">Job Number</label>
                <input type="text" name="job_number" class="w-full border rounded-lg p-2" required>
            </div>

            {{-- TANGGAL START --}}
            <div class="mb-4">
                <label class="font-medium">Tanggal Mulai</label>
                <div class="grid grid-cols-3 gap-2 mt-1">
                    <select name="start_day" class="border rounded-lg p-2" required>
                        <option value="">Hari</option>
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>

                    <select name="start_month" class="border rounded-lg p-2" required>
                        <option value="">Bulan</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>

                    <select name="start_year" class="border rounded-lg p-2" required>
                        <option value="">Tahun</option>
                        @for ($y = date('Y'); $y <= date('Y') + 3; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- TANGGAL END --}}
            <div class="mb-4">
                <label class="font-medium">Tanggal Berakhir</label>
                <div class="grid grid-cols-3 gap-2 mt-1">
                    <select name="end_day" class="border rounded-lg p-2" required>
                        <option value="">Hari</option>
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>

                    <select name="end_month" class="border rounded-lg p-2" required>
                        <option value="">Bulan</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                        @endfor
                    </select>

                    <select name="end_year" class="border rounded-lg p-2" required>
                        <option value="">Tahun</option>
                        @for ($y = date('Y'); $y <= date('Y') + 3; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>


            {{-- TEMPAT --}}
            <div class="mb-4">
                <label class="font-medium">Tempat</label>
                <input type="text" name="tempat" class="w-full border rounded-lg p-2" required>
            </div>

            {{-- JENIS SERTIFIKASI --}}
            <div class="mb-4">
                <label class="font-medium">Jenis Sertifikasi</label>
                <select name="jenis_sertifikasi" class="w-full border rounded-lg p-2">
                    <option value="">-- Pilih Jenis Sertifikasi --</option>
                    <option value="Kementrian">Kementrian</option>
                    <option value="Bnsp">BNSP</option>
                    <option value="Alkon Best Mandiri">Alkon Best Mandiri</option>
                </select>
            </div>
   
            {{-- SERTIFIKASI --}}
            <div class="mb-4">
                <label class="font-medium">Sertifikasi</label>
                <input type="text" name="sertifikasi" class="w-full border rounded-lg p-2">
            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end gap-2 mt-6">
                <a href="{{ route('event-training.index') }}"
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Kembali
                </a>

                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    {{-- SCRIPT: SHOW/HIDE HARGA --}}
    <script>
        const jenisEventSelect = document.getElementById('jenis_event');
        const hargaWrapper = document.getElementById('harga_paket_wrapper');

        jenisEventSelect.addEventListener('change', function() {
            hargaWrapper.style.display = this.value === 'inhouse' ? 'block' : 'none';
        });
    </script>

</x-app-layout>
