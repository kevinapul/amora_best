<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Detail Master Training
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-6 py-8">

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $master->nama_training }}
                </h1>
                <p class="text-gray-600 mt-1">
                    Kategori: {{ $master->kategori ?? '-' }}
                </p>
            </div>

            <a href="{{ route('master-training.index') }}"
               class="px-4 py-2 bg-gray-500 text-white rounded-lg
                      hover:bg-gray-600 transition">
                ← Kembali
            </a>
        </div>

        {{-- SUMMARY --}}
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-500">Total Training</p>
                <p class="text-2xl font-bold text-indigo-600">
                    {{ $master->trainings->count() }}
                </p>
            </div>

            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-500">Dibuat</p>
                <p class="text-base font-semibold">
                    {{ $master->created_at->format('d M Y') }}
                </p>
            </div>

            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-500">Terakhir Update</p>
                <p class="text-base font-semibold">
                    {{ $master->updated_at->format('d M Y') }}
                </p>
            </div>
        </div>

        {{-- TABLE TRAINING --}}
        <div class="bg-white shadow rounded-lg overflow-hidden border">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr class="text-left text-gray-700">
                        <th class="px-4 py-3 w-12">#</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Nama Training</th>
                        <th class="px-4 py-3">Deskripsi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($master->trainings as $i => $t)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                {{ $i + 1 }}
                            </td>

                            <td class="px-4 py-3 font-mono text-sm">
                                {{ $t->code }}
                            </td>

                            <td class="px-4 py-3 font-semibold">
                                {{ $t->name }}
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                {{ $t->description ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                Belum ada training di master ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
