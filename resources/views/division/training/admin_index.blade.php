<x-app-layout>

    <div class="alkon-root py-10">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div class="alkon-status">
                <div>
                    <h2 class="text-xl font-semibold">
                        Administrasi Sertifikat
                    </h2>
                    <p class="text-sm text-gray-200">
                        Monitoring & input sertifikat peserta training
                    </p>
                </div>
                                <a href="{{ route('division.tools') }}"
                    class="alkon-btn-secondary text-xs bg-white/20 text-white hover:bg-white/30 border border-white/30">
                    ← Tools Divisi
                </a>
            </div>

            {{-- FILTER BULAN --}}
            <div class="alkon-panel">
                <div class="alkon-panel-header">
                    Filter Bulan Training
                </div>

                <div class="alkon-panel-body flex items-center gap-4">

                    <form method="GET" class="flex items-center gap-3">

                        {{-- BULAN --}}
                        <select name="month" onchange="this.form.submit()" class="alkon-input w-56">

                            @for ($m = 1; $m <= 12; $m++)
                                @php
                                    $val = date('Y') . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                                    $label = \Carbon\Carbon::create()->month($m)->format('F');
                                @endphp

                                <option value="{{ $val }}" {{ $month == $val ? 'selected' : '' }}>
                                    {{ $label }} {{ date('Y') }}
                                </option>
                            @endfor

                        </select>

                    </form>

                    <div class="text-sm text-gray-500">
                        Menampilkan training selesai pada bulan terpilih
                    </div>

                </div>
            </div>


            {{-- LIST GROUP --}}
            <div class="space-y-4">

                @forelse($groups as $g)
                    <div class="alkon-panel hover:shadow-md transition">

                        <div class="alkon-panel-body flex justify-between items-center">

                            {{-- LEFT --}}
                            <div class="space-y-1">
                                <div class="text-lg font-semibold text-gray-800">
                                    {{ $g->masterTraining->nama_training ?? '-' }}
                                </div>

                                <div class="text-sm text-gray-500">
                                    Job Number:
                                    <span class="font-semibold text-gray-700">
                                        {{ $g->job_number }}
                                    </span>
                                </div>
                            </div>

                            {{-- RIGHT --}}
                            <div class="flex items-center gap-6">

                                {{-- STATS --}}
                                <div class="text-right text-sm">
                                    <div class="text-gray-500">Peserta</div>
                                    <div class="font-semibold text-gray-800">
                                        {{ $g->total_peserta ?? '-' }}
                                    </div>
                                </div>

                                <div class="text-right text-sm">
                                    <div class="text-gray-500">Sertifikat</div>
                                    <div class="font-semibold text-green-700">
                                        {{ $g->sertifikat_siap ?? 0 }}
                                    </div>
                                </div>

                                {{-- BUTTON --}}
                                <a href="{{ route('division.training.group', $g->id) }}" class="alkon-btn-primary">
                                    Kelola Sertifikat →
                                </a>

                            </div>

                        </div>

                    </div>
                @empty

                    <div class="alkon-panel">
                        <div class="alkon-panel-body text-center text-gray-500 py-12">
                            Tidak ada training selesai bulan ini
                        </div>
                    </div>
                @endforelse
                @if ($groups->hasPages())
                    <div class="mt-6 flex justify-center">
                        {{ $groups->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <style>
    .pagination {
        display: flex;
        gap: 8px;
    }

    .pagination li {
        list-style: none;
    }

    .pagination li a,
    .pagination li span {
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        font-size: 14px;
    }

    .pagination li span[aria-current="page"] {
        background: #0f3d2e;
        color: white;
        border-color: #0f3d2e;
    }
</style>

</x-app-layout>
