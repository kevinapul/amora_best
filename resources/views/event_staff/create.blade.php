<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Instruktur / Training Officer
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-md sm:rounded-lg p-6">

                {{-- INFO EVENT --}}
                <div class="mb-6">
                    <h3 class="text-lg font-semibold">
                        {{ $event->training->name }}
                    </h3>
                    <p class="text-gray-600 text-sm">
                        {{ $event->tanggal }} — {{ $event->tempat }}
                    </p>
                </div>

                <form action="{{ route('event-staff.store', $event->id) }}" method="POST">
                    @csrf

                    {{-- ================= INSTRUKTUR ================= --}}
                    <div class="mb-5">
                        <label class="block font-semibold mb-1">
                            Instruktur <span class="text-xs text-gray-500">(maks. 2)</span>
                        </label>

                        <select name="instrukturs[]" multiple
                                class="w-full border rounded p-2"
                                size="3">
                            @foreach ([
                                'Windhian Krisanto',
                                'Abdul Rasid',
                                'Edy Sulistiono'
                            ] as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>

                        <p class="text-xs text-gray-500 mt-1">
                            Tahan <b>Ctrl</b> (Windows) / <b>Cmd</b> (Mac) untuk memilih lebih dari satu
                        </p>

                        @error('instrukturs')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ================= TRAINING OFFICER ================= --}}
                    <div class="mb-6">
                        <label class="block font-semibold mb-1">
                            Training Officer <span class="text-xs text-gray-500">(maks. 2)</span>
                        </label>

                        <select name="training_officers[]" multiple
                                class="w-full border rounded p-2"
                                size="5">
                            @foreach ([
                                'Edy Sulistiono',
                                'Rizky',
                                'Sahrul Alam',
                                'Sausan',
                                'Nyoman'
                            ] as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>

                        @error('training_officers')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ================= ACTION ================= --}}
                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Simpan
                        </button>

                        <a href="{{ route('event-staff.show', $event->id) }}"
                           class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Batal
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- ================= LIMIT SELECT MAX 2 (FRONTEND GUARD) ================= --}}
    <script>
        document.querySelectorAll('select[multiple]').forEach(select => {
            select.addEventListener('change', function () {
                if (this.selectedOptions.length > 2) {
                    alert('Maksimal 2 orang per pilihan');
                    this.selectedOptions[this.selectedOptions.length - 1].selected = false;
                }
            });
        });
    </script>

</x-app-layout>
