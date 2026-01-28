<x-app-layout>

    <div class="alkon-root">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- ================= HEADER CARD ================= -->
            <div class="alkon-panel mb-6">
                <div class="alkon-panel-body flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-[var(--alkon-text)]">
                            📚 {{ $master->nama_training }}
                        </h1>
                        <p class="text-sm text-[var(--alkon-muted)] mt-1">
                            Kategori: {{ $master->kategori ?? '-' }}
                        </p>
                    </div>

                    <a href="{{ route('master-training.index') }}"
                       class="alkon-btn-secondary">
                        ← Kembali
                    </a>
                </div>
            </div>

            <!-- ================= SUMMARY ================= -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">

                <div class="alkon-panel">
                    <div class="alkon-panel-body">
                        <p class="text-sm text-[var(--alkon-muted)]">
                            Total Training
                        </p>
                        <p class="text-3xl font-bold text-[var(--alkon-green)] mt-1">
                            {{ $master->trainings->count() }}
                        </p>
                    </div>
                </div>

                <div class="alkon-panel">
                    <div class="alkon-panel-body">
                        <p class="text-sm text-[var(--alkon-muted)]">
                            Dibuat
                        </p>
                        <p class="text-base font-semibold text-gray-800 mt-1">
                            {{ $master->created_at->format('d M Y') }}
                        </p>
                    </div>
                </div>

                <div class="alkon-panel">
                    <div class="alkon-panel-body">
                        <p class="text-sm text-[var(--alkon-muted)]">
                            Terakhir Update
                        </p>
                        <p class="text-base font-semibold text-gray-800 mt-1">
                            {{ $master->updated_at->format('d M Y') }}
                        </p>
                    </div>
                </div>

            </div>

            <!-- ================= TABLE TRAINING ================= -->
            <div class="alkon-panel">
                <div class="overflow-x-auto border-2 border-gray-300 rounded-xl">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 w-12 text-left">#</th>
                                <th class="px-4 py-3 text-left">Code</th>
                                <th class="px-4 py-3 text-left">Nama Training</th>
                                <th class="px-4 py-3 text-left">Deskripsi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($master->trainings as $i => $t)
                                <tr class="border-t hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        {{ $i + 1 }}
                                    </td>

                                    <td class="px-4 py-3 font-mono text-sm text-gray-700">
                                        {{ $t->code }}
                                    </td>

                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ $t->name }}
                                    </td>

                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $t->description ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        Belum ada training di master ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</x-app-layout>
