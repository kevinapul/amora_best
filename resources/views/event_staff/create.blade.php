<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Instruktur / Training Officer
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-md sm:rounded-lg p-6">
                
                <!-- Info Event -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold">{{ $event->training->name }}</h3>
                    <p class="text-gray-600 text-sm">
                        {{ $event->tanggal }} — {{ $event->tempat }}
                    </p>
                </div>

                <form action="{{ route('event-staff.store', $event->id) }}" method="POST">
                    @csrf

                    <!-- Nama -->
                    <div class="mb-4">
                        <label class="block font-semibold">Nama Staf</label>
                        <input type="text" name="name"
                               class="w-full border rounded p-2"
                               required>
                    </div>

                    <!-- Nomor HP -->
                    <div class="mb-4">
                        <label class="block font-semibold">Nomor HP</label>
                        <input type="text" name="phone"
                               class="w-full border rounded p-2">
                    </div>

                    <!-- Role -->
                    <div class="mb-4">
                        <label class="block font-semibold">Peran</label>
                        <select name="role" class="w-full border rounded p-2" required>
                            <option value="">-- Pilih Peran --</option>
                            <option value="Instruktur">Instruktur</option>
                            <option value="Training Officer">Training Officer</option>
                        </select>
                    </div>

                    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Simpan
                    </button>

                    <a href="{{ route('event-staff.show', $event->id) }}"
                       class="ml-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Batal
                    </a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
