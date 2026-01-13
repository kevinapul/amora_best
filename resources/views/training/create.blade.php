<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Tambah Master Training
        </h2>
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

        <form method="POST" action="{{ route('master-training.store') }}">
            @csrf

            {{-- ================= MASTER INFO ================= --}}
            <div class="mb-6">
                <label class="font-medium">Nama Master Training *</label>
                <input type="text" name="nama_training"
                       class="w-full border rounded-lg p-2 mt-1"
                       placeholder="Contoh: Rigging Forklift"
                       value="{{ old('nama_training') }}"
                       required>
            </div>

            <div class="mb-8">
                <label class="font-medium">Kategori</label>
                <input type="text" name="kategori"
                       class="w-full border rounded-lg p-2 mt-1"
                       placeholder="Contoh: Alat Berat"
                       value="{{ old('kategori') }}">
            </div>

            <hr class="my-6">

            {{-- ================= TRAINING LIST ================= --}}
            <h3 class="text-lg font-semibold mb-4">
                Daftar Training (Anak dari Master)
            </h3>

            <div id="training-wrapper">
                {{-- TRAINING ITEM --}}
                <div class="training-item border rounded-lg p-4 mb-4 bg-gray-50">
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="text-sm font-medium">Kode *</label>
                            <input type="text" name="codes[]"
                                   class="w-full border rounded p-2 mt-1"
                                   placeholder="RF-BASIC"
                                   required>
                        </div>

                        <div>
                            <label class="text-sm font-medium">Nama Training *</label>
                            <input type="text" name="names[]"
                                   class="w-full border rounded p-2 mt-1"
                                   placeholder="Rigging Forklift Basic"
                                   required>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Deskripsi</label>
                        <textarea name="descriptions[]" rows="2"
                                  class="w-full border rounded p-2 mt-1"
                                  placeholder="Deskripsi singkat training"></textarea>
                    </div>
                </div>
            </div>

            {{-- ADD BUTTON --}}
            <button type="button"
                    onclick="addTraining()"
                    class="mb-6 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                + Tambah Training
            </button>

            {{-- ACTION --}}
            <div class="flex justify-end gap-2 mt-8">
                <a href="{{ route('master-training.index') }}"
                   class="px-4 py-2 bg-gray-500 text-white rounded-lg">
                    Kembali
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg">
                    Simpan Master Training
                </button>
            </div>
        </form>
    </div>

    {{-- ================= SCRIPT ================= --}}
    <script>
        function addTraining() {
            const wrapper = document.getElementById('training-wrapper');

            const div = document.createElement('div');
            div.className = 'training-item border rounded-lg p-4 mb-4 bg-gray-50';

            div.innerHTML = `
                <div class="flex justify-end mb-2">
                    <button type="button"
                            onclick="this.closest('.training-item').remove()"
                            class="text-red-600 text-sm">
                        Hapus
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="text-sm font-medium">Kode *</label>
                        <input type="text" name="codes[]"
                               class="w-full border rounded p-2 mt-1"
                               placeholder="RF-ADV"
                               required>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Nama Training *</label>
                        <input type="text" name="names[]"
                               class="w-full border rounded p-2 mt-1"
                               placeholder="Rigging Forklift Advanced"
                               required>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium">Deskripsi</label>
                    <textarea name="descriptions[]" rows="2"
                              class="w-full border rounded p-2 mt-1"
                              placeholder="Deskripsi singkat training"></textarea>
                </div>
            `;

            wrapper.appendChild(div);
        }
    </script>
</x-app-layout>
