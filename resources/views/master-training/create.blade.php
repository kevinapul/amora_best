<x-app-layout>

    <div class="alkon-root">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            <!-- ================= HEADER CARD ================= -->
            <div class="alkon-panel mb-8">
                <div class="alkon-panel-body flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-[var(--alkon-text)]">
                            ➕ Tambah Master Training
                        </h1>
                        <p class="text-sm text-[var(--alkon-muted)] mt-1">
                            Buat master training beserta daftar training di dalamnya
                        </p>
                    </div>

                    <a href="{{ route('master-training.index') }}"
                       class="alkon-btn-secondary">
                        ← Kembali
                    </a>
                </div>
            </div>

            <!-- ================= ERROR ================= -->
            @if ($errors->any())
                <div class="alkon-panel mb-6 border-red-300 bg-red-50">
                    <div class="alkon-panel-body">
                        <ul class="list-disc list-inside text-red-700 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- ================= FORM ================= -->
            <form method="POST" action="{{ route('master-training.store') }}">
                @csrf

                <!-- MASTER INFO -->
                <div class="alkon-panel mb-8">
                    <div class="alkon-panel-body space-y-6">

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                Nama Master Training <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_training"
                                   class="alkon-input"
                                   placeholder="Contoh: Rigging Forklift"
                                   value="{{ old('nama_training') }}"
                                   required>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                Kategori
                            </label>
                            <input type="text" name="kategori"
                                   class="alkon-input"
                                   placeholder="Contoh: Alat Berat"
                                   value="{{ old('kategori') }}">
                        </div>

                    </div>
                </div>

                <!-- TRAINING LIST -->
                <div class="alkon-panel mb-8">
                    <div class="alkon-panel-body">

                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            📋 Daftar Training
                        </h3>

                        <div id="training-wrapper" class="space-y-4">

                            <!-- TRAINING ITEM -->
                            <div class="training-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-sm font-medium">Kode *</label>
                                        <input type="text" name="codes[]"
                                               class="alkon-input mt-1"
                                               placeholder="RF-BASIC"
                                               required>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium">Nama Training *</label>
                                        <input type="text" name="names[]"
                                               class="alkon-input mt-1"
                                               placeholder="Rigging Forklift Basic"
                                               required>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium">Deskripsi</label>
                                    <textarea name="descriptions[]" rows="2"
                                              class="alkon-input mt-1"
                                              placeholder="Deskripsi singkat training"></textarea>
                                </div>
                            </div>

                        </div>

                        <button type="button"
                                onclick="addTraining()"
                                class="mt-6 alkon-btn-secondary">
                            + Tambah Training
                        </button>

                    </div>
                </div>

                <!-- ACTION -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('master-training.index') }}"
                       class="alkon-btn-secondary">
                        Batal
                    </a>

                    <button type="submit"
                            class="alkon-btn-primary">
                        Simpan Master Training
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- ================= SCRIPT (ASLI, TIDAK DIUBAH) ================= -->
    <script>
        function addTraining() {
            const wrapper = document.getElementById('training-wrapper');

            const div = document.createElement('div');
            div.className = 'training-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50';

            div.innerHTML = `
                <div class="flex justify-end mb-3">
                    <button type="button"
                            onclick="this.closest('.training-item').remove()"
                            class="text-red-600 text-sm font-medium">
                        Hapus
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-sm font-medium">Kode *</label>
                        <input type="text" name="codes[]"
                               class="alkon-input mt-1"
                               placeholder="RF-ADV"
                               required>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Nama Training *</label>
                        <input type="text" name="names[]"
                               class="alkon-input mt-1"
                               placeholder="Rigging Forklift Advanced"
                               required>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium">Deskripsi</label>
                    <textarea name="descriptions[]" rows="2"
                              class="alkon-input mt-1"
                              placeholder="Deskripsi singkat training"></textarea>
                </div>
            `;

            wrapper.appendChild(div);
        }
    </script>

</x-app-layout>
