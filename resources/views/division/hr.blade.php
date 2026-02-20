<x-app-layout>
    <div class="alkon-root py-10">
        <div class="max-w-7xl mx-auto space-y-6">


            {{-- HEADER --}}
            <div class="alkon-status">
                <div>
                    <h2 class="text-xl font-semibold">
                        Dashboard HR
                    </h2>
                    <p class="text-sm text-gray-200">
                        Monitoring absensi karyawan real-time
                    </p>
                </div>
                <a href="{{ route('division.tools') }}"
                    class="alkon-btn-secondary text-xs bg-white/20 text-white hover:bg-white/30 border border-white/30">
                    ← Tools Divisi
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- ================= AKTIF ================= --}}
                <div class="alkon-panel">

                    <div class="alkon-panel-header flex justify-between">
                        <span>👨‍💼 Sedang Aktif</span>
                        <span class="text-xs text-gray-500">
                            Live tracking
                        </span>
                    </div>

                    <div class="alkon-panel-body overflow-x-auto">

                        <table class="w-full text-sm">
                            <thead class="border-b text-gray-500">
                                <tr>
                                    <th class="py-3 text-left">Nama</th>
                                    <th>Jam Masuk</th>
                                    <th>Durasi</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($activeUsers as $user)
                                    <tr class="border-b hover:bg-gray-50">

                                        <td class="py-3 font-semibold text-gray-800">
                                            {{ $user->name }}
                                        </td>

                                        <td class="text-center">
                                            {{ $user->check_in->format('H:i') }}
                                        </td>

                                        <td class="text-center">
                                            <span class="timer font-mono text-green-700 font-semibold"
                                                data-start="{{ $user->check_in->timestamp }}">
                                                --:--:--
                                            </span>
                                        </td>

                                        <td class="text-right">
                                            <form method="POST" action="{{ route('hr.forceCheckout', $user->id) }}">
                                                @csrf
                                                <button
                                                    class="alkon-btn-secondary text-xs bg-red-500 text-white hover:bg-red-600">
                                                    Force Checkout
                                                </button>
                                            </form>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400 py-6">
                                            Tidak ada karyawan aktif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>


                {{-- ================= BELUM ABSEN ================= --}}
                <div class="alkon-panel">

                    <div class="alkon-panel-header flex justify-between">
                        <span>⏳ Belum Absen</span>
                        <span class="text-xs text-gray-500">
                            Reminder manual
                        </span>
                    </div>

                    <div class="alkon-panel-body overflow-x-auto">

                        <table class="w-full text-sm">
                            <thead class="border-b text-gray-500">
                                <tr>
                                    <th class="py-3 text-left">Nama</th>
                                    <th>Divisi</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($pendingUsers as $user)
                                    <tr class="border-b hover:bg-gray-50">

                                        <td class="py-3 font-semibold text-gray-800">
                                            {{ $user->name }}
                                        </td>

                                        <td class="text-center">
                                            {{ $user->division }}
                                        </td>

                                        <td class="text-right">
                                            <button class="alkon-btn-primary text-xs"
                                                onclick="alert('Reminder dikirim ke {{ $user->name }}')">
                                                Kirim Reminder
                                            </button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-gray-400 py-6">
                                            Semua sudah absen
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>


        </div>
    </div>

    {{-- ================= TIMER REALTIME ================= --}}

    <script>
        function formatHMS(sec) {
            return new Date(sec * 1000).toISOString().substr(11, 8);
        }

        document.querySelectorAll('.timer').forEach(el => {
            let start = parseInt(el.dataset.start);

            setInterval(() => {
                let seconds = Math.floor(Date.now() / 1000) - start;
                el.textContent = formatHMS(seconds);
            }, 1000);
        });
    </script>

</x-app-layout>
