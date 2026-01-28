<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Tools Divisi
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- HR -->
            <a href="{{ route('division.hr') }}" 
               class="block bg-white p-6 shadow-md rounded-lg hover:bg-gray-100 transition">
                <h4 class="text-lg font-semibold mb-2">👥 HR</h4>
                <p class="text-gray-600 text-sm">Absensi, laporan karyawan.</p>
            </a>

            <!-- Training / Sertifikasi -->
            <a href="{{ route('division.training') }}"
               class="block bg-white p-6 shadow-md rounded-lg hover:bg-gray-100 transition ">
                <h4 class="text-lg font-semibold mb-2">📚 Sertifikasi</h4>
                <p class="text-gray-600 text-sm"> Monitoring & pembuatan sertifikat
                </p>
            </a>

            <!-- Ops -->
                    <a href="{{ route('event-staff.events') }}"
                        class="block bg-white p-6 shadow-md sm:rounded-lg hover:bg-gray-100 transition">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">👨‍🏫 Instruktur & Training Officer</h4>
                        <p class="text-gray-600 text-sm">Kelola instruktur.</p>
                    </a>

                                <a href="{{ route('laporan') }}"
                        class="block bg-white p-6 shadow-md sm:rounded-lg hover:bg-gray-100 transition">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">📊 Laporan</h4>
                        <p class="text-gray-600 text-sm">Data laporan bulanan.</p>
                    </a>
        </div>
    </div>
</x-app-layout>
