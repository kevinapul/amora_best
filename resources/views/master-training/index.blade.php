<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Master Training
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-6 py-8">

        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 rounded text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- HEADER ACTION --}}
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">
                Daftar master training dan jumlah training di dalamnya
            </p>

            <a href="{{ route('master-training.create') }}"
               class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold
                      hover:bg-indigo-700 transition">
                + Tambah Master Training
            </a>
        </div>

        {{-- TABLE --}}
        <div class="bg-white shadow rounded-lg overflow-hidden border">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr class="text-left text-gray-700">
                        <th class="px-4 py-3 w-12">#</th>
                        <th class="px-4 py-3">Nama Master Training</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-center">Jumlah Training</th>
                        <th class="px-4 py-3 text-center w-40">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($masters as $index => $master)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                {{ $masters->firstItem() + $index }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $master->nama_training }}
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                {{ $master->kategori ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-1 text-sm
                                             bg-blue-100 text-blue-700 rounded">
                                    {{ $master->trainings_count }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center space-x-2">
                                <a href="{{ route('master-training.show', $master->id) }}"
                                   class="text-indigo-600 hover:underline">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                Belum ada master training
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6">
            {{ $masters->links() }}
        </div>

    </div>
</x-app-layout>
