<x-app-layout>
<div class="alkon-root py-10">
<div class="max-w-6xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="alkon-status">
        <div>
            <h2 class="text-xl font-semibold">
                Tools Divisi
            </h2>
            <p class="text-sm text-gray-200">
                Akses cepat ke seluruh modul internal perusahaan
            </p>
        </div>

            <a href="{{ route('dashboard') }}"
       class="alkon-btn-secondary text-xs bg-white/20 text-white hover:bg-white/30 border border-white/30">
        ← Dashboard
    </a>
    </div>

    {{-- GRID MENU --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- HR --}}
        <a href="{{ route('division.hr') }}" class="alkon-tool group">
            <div class="flex items-center gap-4">
                <div class="text-3xl">👥</div>
                <div>
                    <h4 class="font-semibold text-gray-800 group-hover:text-[#0f3d2e]">
                        HR Management
                    </h4>
                    <p class="text-sm text-gray-500">
                        Absensi & monitoring karyawan
                    </p>
                </div>
            </div>
        </a>

        {{-- SERTIFIKAT --}}
        <a href="{{ route('division.training') }}" class="alkon-tool group">
            <div class="flex items-center gap-4">
                <div class="text-3xl">📚</div>
                <div>
                    <h4 class="font-semibold text-gray-800 group-hover:text-[#0f3d2e]">
                        Sertifikasi Training
                    </h4>
                    <p class="text-sm text-gray-500">
                        Monitoring & pembuatan sertifikat
                    </p>
                </div>
            </div>
        </a>

        {{-- INSTRUKTUR --}}
        <a href="{{ route('event-staff.events') }}" class="alkon-tool group">
            <div class="flex items-center gap-4">
                <div class="text-3xl">👨‍🏫</div>
                <div>
                    <h4 class="font-semibold text-gray-800 group-hover:text-[#0f3d2e]">
                        Instruktur & Officer
                    </h4>
                    <p class="text-sm text-gray-500">
                        Kelola instruktur & training officer
                    </p>
                </div>
            </div>
        </a>

        {{-- LAPORAN --}}
        <a href="{{ route('laporan') }}" class="alkon-tool group">
            <div class="flex items-center gap-4">
                <div class="text-3xl">📊</div>
                <div>
                    <h4 class="font-semibold text-gray-800 group-hover:text-[#0f3d2e]">
                        Laporan & Statistik
                    </h4>
                    <p class="text-sm text-gray-500">
                        Data laporan bulanan & performa
                    </p>
                </div>
            </div>
        </a>

    </div>

</div>
</div>
</x-app-layout>