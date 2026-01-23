<x-app-layout>
    <div style="transform: scale(1.10); transform-origin: top left; width: 91%;">

        @php
            $attendanceToday = \App\Models\Attendance::where('user_id', Auth::id())->latest()->first();

            if (!$attendanceToday) {
                $hasCheckedIn = false;
                $checkedInSeconds = 0;
            } elseif ($attendanceToday->created_at->isToday() && !$attendanceToday->clock_out) {
                $hasCheckedIn = true;
                $checkedInSeconds = now()->diffInSeconds($attendanceToday->created_at);
            } else {
                $hasCheckedIn = false;
                $checkedInSeconds = 0;
            }
        @endphp

        @php
            $statusText = 'Belum Tercapai';
            $statusColor = 'text-gray-500';

            if ($totalRealisasi >= $targetAtas) {
                $statusText = 'Target Atas Tercapai';
                $statusColor = 'text-green-700';
            } elseif ($totalRealisasi >= $targetTengah) {
                $statusText = 'Target Tengah Tercapai';
                $statusColor = 'text-green-600';
            } elseif ($totalRealisasi >= $targetBawah) {
                $statusText = 'Target Bawah Tercapai';
                $statusColor = 'text-green-500';
            }
        @endphp

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Welcome Box -->
                <div class="bg-white shadow-md sm:rounded-lg p-6 mb-4 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            Selamat datang, {{ Auth::user()->name }} 👋
                        </h3>
                        <p class="text-gray-600">Kamu berhasil login. Silakan pilih menu di bawah untuk mulai bekerja.
                        </p>
                    </div>

                    <div>
                        <button id="absenButton" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            {{ $hasCheckedIn ? 'Sudah Absen: --:--:--' : 'Absen' }}
                        </button>
                    </div>
                </div>
                <div x-data="{ open: false }" class="bg-white shadow-md rounded-lg mb-6">

                    {{-- HEADER --}}
                    <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-gray-800">
                                🎯 Target Tahunan
                            </span>

                            {{-- STATUS ICON --}}
                            <span class="text-sm font-semibold {{ $statusColor }}">
                                @if ($totalRealisasi >= $targetAtas)
                                    🏆 Target Atas Tercapai
                                @elseif ($totalRealisasi >= $targetTengah)
                                    🎯 Target Tengah Tercapai
                                @elseif ($totalRealisasi >= $targetBawah)
                                    ✔ Target Bawah Tercapai
                                @else
                                    ⏳ Belum Tercapai
                                @endif
                            </span>

                        </div>

                        {{-- CHEVRON --}}
                        <svg :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 transition-transform"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- CONTENT --}}
                    <div x-show="open" x-transition class="border-t px-6 py-4">
                        @include('dashboard.targets')
                    </div>

                </div>

                <div x-data="{ open: false }" class="bg-white shadow-md rounded-lg">

                    {{-- HEADER --}}
                    <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="text-lg font-semibold text-gray-800">
                            📋 Training Bulan Ini
                        </span>

                        <svg :class="{ 'rotate-180': open }" class="w-5 h-5 text-gray-500 transition-transform"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- CONTENT --}}
                    <div x-show="open" x-transition class="border-t px-6 py-4">
                        @include('dashboard.tables')
                    </div>

                </div>


                <!-- SPACER — JANGAN DIHAPUS -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10"></div>

                <div
                    class="quick-menu grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6
            {{ $hasCheckedIn ? '' : 'opacity-50 pointer-events-none' }}">

                    <a href="{{ route('master-training.index') }}"
                        class="block bg-white p-6 shadow-md sm:rounded-lg hover:bg-gray-100 transition">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">📚 Master Training</h4>
                        <p class="text-gray-600 text-sm">Kelola data training.</p>
                    </a>

                    <a href="{{ route('event-training.index') }}"
                        class="block bg-white p-6 shadow-md sm:rounded-lg hover:bg-gray-100 transition">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">📅 Event Training</h4>
                        <p class="text-gray-600 text-sm">Input jadwal training baru.</p>
                    </a>

                    <a href="{{ route('event-staff.events') }}"
                        class="block bg-white p-6 shadow-md sm:rounded-lg hover:bg-gray-100 transition">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">👨‍🏫 Instruktur & Training Officer</h4>
                        <p class="text-gray-600 text-sm">Kelola instruktur.</p>
                    </a>

                    <a href="{{ route('division.tools') }}"
                        class="block bg-white p-6 shadow-md sm:rounded-lg hover:bg-gray-100 transition">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">🏢 Tools Divisi</h4>
                        <p class="text-gray-600 text-sm">Menu khusus setiap divisi.</p>
                    </a>

                    <a href="{{ route('laporan') }}"
                        class="block bg-white p-6 shadow-md sm:rounded-lg hover:bg-gray-100 transition">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">📊 Laporan</h4>
                        <p class="text-gray-600 text-sm">Data laporan bulanan.</p>
                    </a>
                </div>
            </div>
        </div> <!-- TUTUP div transform scale -->

        <!-- Modal Check-in -->
        <div id="absenModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded shadow-lg w-96">
                <h3 class="text-lg font-semibold mb-2">Mau mengerjakan apa hari ini?</h3>
                <textarea id="activityInput" class="w-full border rounded p-2 mb-4" placeholder="Tulis aktivitasmu"></textarea>
                <button id="submitAbsen" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                    Absen
                </button>
            </div>
        </div>

        <!-- Modal Checkout -->
        <div id="checkoutModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 transition-opacity duration-200">
            <div class="bg-white p-6 rounded shadow-lg w-96 animate-scale">
                <h3 class="text-lg font-semibold mb-2">Akhiri pekerjaan hari ini?</h3>
                <p class="text-gray-700 mb-4 text-sm">Waktu kerjamu akan dihitung dan dicatat.</p>

                <div class="flex gap-2">
                    <button id="confirmCheckout"
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 w-full">
                        Ya, akhiri
                    </button>
                    <button id="cancelCheckout" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 w-full">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <script>
            let seconds = {{ $checkedInSeconds }};
            let hasCheckedIn = {{ $hasCheckedIn ? 'true' : 'false' }};
            let timerInterval;
            const absenButton = document.getElementById('absenButton');

            function formatHMS(sec) {
                return new Date(sec * 1000).toISOString().substr(11, 8);
            }

            function renderTimer() {
                timerInterval = setInterval(() => {
                    seconds++;
                    absenButton.textContent = "Sudah Absen: " + formatHMS(seconds);
                }, 1000);
            }

            if (hasCheckedIn) renderTimer();

            // ----- Modal show/hide -----
            const checkoutModal = document.getElementById('checkoutModal');
            const confirmCheckout = document.getElementById('confirmCheckout');
            const cancelCheckout = document.getElementById('cancelCheckout');

            cancelCheckout.addEventListener('click', () => {
                checkoutModal.classList.add('hidden');
                renderTimer();
            });

            // Tombol absen
            absenButton.addEventListener('click', () => {
                if (hasCheckedIn) {
                    clearInterval(timerInterval);
                    checkoutModal.classList.remove('hidden');
                    return;
                }
                document.getElementById('absenModal').classList.remove('hidden');
            });

            // Tombol submit absen
            document.getElementById('submitAbsen').addEventListener('click', async () => {
                const activity = document.getElementById('activityInput').value.trim();

                if (!activity) {
                    alert("Aktivitas tidak boleh kosong");
                    return;
                }

                try {
                    const res = await fetch("{{ route('attendances.store') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            activity
                        })
                    });

                    const data = await res.json();

                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || "Gagal absen");
                    }
                } catch (err) {
                    alert("Gagal terhubung ke server");
                }
            });

            // Checkout confirm
            confirmCheckout.addEventListener('click', async () => {
                confirmCheckout.disabled = true;

                try {
                    const res = await fetch("{{ route('attendances.checkout') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        }
                    });

                    const data = await res.json();

                    if (data.success) {
                        seconds = data.duration ?? seconds;
                        hasCheckedIn = false;
                        absenButton.textContent = "Durasi kerja terakhir: " + formatHMS(seconds);
                        document.querySelector('.quick-menu').classList.add('opacity-50', 'pointer-events-none');
                    } else {
                        alert(data.message || 'Gagal checkout');
                        renderTimer();
                    }

                } catch (err) {
                    alert('Gagal menghubungi server.');
                    renderTimer();
                } finally {
                    checkoutModal.classList.add('hidden');
                    confirmCheckout.disabled = false;
                }
            });
        </script>

        <style>
            .animate-scale {
                animation: scaleIn .15s ease-out;
            }

            @keyframes scaleIn {
                from {
                    transform: scale(.9);
                    opacity: 0;
                }

                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }
        </style>

</x-app-layout>
