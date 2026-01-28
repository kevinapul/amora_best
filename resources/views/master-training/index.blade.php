<x-app-layout>

    <div class="alkon-root">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- ================= HEADER CARD ================= -->
            <div class="alkon-panel mb-6">
                <div class="alkon-panel-body flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-[var(--alkon-text)]">
                            📚 Master Training
                        </h1>
                        <p class="text-sm text-[var(--alkon-muted)] mt-1">
                            Daftar master training dan jumlah training di dalamnya
                        </p>
                    </div>

                    <a href="{{ route('master-training.create') }}" class="alkon-btn-primary">
                        + Tambah Master Training
                    </a>
                </div>
            </div>

            <!-- ================= FLASH MESSAGE ================= -->
            @if (session('success'))
                <div class="alkon-panel mb-6 border-green-300 bg-green-50">
                    <div class="alkon-panel-body text-green-700 font-medium">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <!-- ================= TABLE ================= -->
            <div class="alkon-panel">
                <div class="overflow-x-auto border-2 border-gray-300 rounded-xl">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 w-12 text-left">#</th>
                                <th class="px-4 py-3 text-left">Nama Master Training</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-center">Jumlah Training</th>
                                <th class="px-4 py-3 text-center w-32">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($masters as $index => $master)
                                <tr class="border-t hover:bg-gray-50 transition">
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
                                        <span
                                            class="inline-flex items-center px-3 py-1
                                                     rounded-full text-sm font-medium
                                                     bg-green-100 text-green-700">
                                            {{ $master->trainings_count }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('master-training.show', $master->id) }}"
                                            class="alkon-btn-secondary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        Belum ada master training
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-6">
                <a href="{{ route('dashboard') }}" class="alkon-btn-secondary">
                    ← Kembali
                </a>
            </div>


            <!-- ================= PAGINATION ================= -->
            <div class="mt-6">
                {{ $masters->links() }}
            </div>

        </div>
    </div>

</x-app-layout>
