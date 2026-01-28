<x-app-layout>
    <div style="transform: scale(1.05); transform-origin: top left; width: 95%;" class="alkon-root">

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

        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- ================= STATUS BAR ================= -->
                <div class="alkon-status mb-10">
                    <div>
                        <h2 class="alkon-welcome">
                            Selamat datang, {{ Auth::user()->name }}
                        </h2>
                        <p class="alkon-muted">
                            Silakan mulai aktivitas kerjamu hari ini.
                        </p>
                    </div>

                    <button id="absenButton" class="alkon-absen-btn">
                        {{ $hasCheckedIn ? 'Sudah Absen: --:--:--' : 'Mulai Absen' }}
                    </button>
                </div>

                <!-- ================= PERFORMANCE ================= -->
                <div class="flex flex-col gap-6 mb-12">

                    <!-- TARGET -->
                    <div x-data="{ open: false }" class="alkon-panel">
                        <button @click="open = !open"
                            class="alkon-panel-header">
                            <div>
                                <h3>🎯 Target Tahunan</h3>
                                <span class="alkon-status-text {{ $statusColor }}">
                                    {{ $statusText }}
                                </span>
                            </div>

                            <svg :class="{ 'rotate-180': open }"
                                class="w-5 h-5 transition-transform"
                                fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition class="alkon-panel-body">
                            @include('dashboard.targets')
                        </div>
                    </div>

                    <!-- TRAINING -->
                    <div x-data="{ open: false }" class="alkon-panel">
                        <button @click="open = !open"
                            class="alkon-panel-header">
                            <h3>📋 Training Bulan Ini</h3>

                            <svg :class="{ 'rotate-180': open }"
                                class="w-5 h-5 transition-transform"
                                fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition class="alkon-panel-body">
                            @include('dashboard.tables')
                        </div>
                    </div>

                </div>

                <!-- ================= QUICK MENU ================= -->
                <div
                    class="quick-menu grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5
                    {{ $hasCheckedIn ? '' : 'opacity-50 pointer-events-none' }}">

                    <a href="{{ route('master-training.index') }}" class="alkon-tool">
                        <h4>📚 Master Training</h4>
                        <p>Kelola data training.</p>
                    </a>

                    <a href="{{ route('event-training.index') }}" class="alkon-tool">
                        <h4>📅 Event Training</h4>
                        <p>Input jadwal training baru.</p>
                    </a>

                    <a href="{{ route('division.tools') }}" class="alkon-tool">
                        <h4>🏢 Tools Divisi</h4>
                        <p>Menu khusus setiap divisi.</p>
                    </a>
                </div>

            </div>
        </div>


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
    :root {
        --alkon-green: #0f3d2e;
        --alkon-bg: #f5f7f6;
        --alkon-border: #e5e7eb;
        --alkon-text: #111827;
        --alkon-muted: #6b7280;
    }

    .alkon-root {
        background: var(--alkon-bg);
        min-height: 100vh;
    }

    /* STATUS BAR */
    .alkon-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        background: linear-gradient(180deg, #0f3d2e, #0b2f24);
        padding: 28px 32px;
        border-radius: 18px;
        color: white;
    }

    .alkon-welcome {
        font-size: 1.25rem;
        font-weight: 600;
    }

    .alkon-muted {
        font-size: .9rem;
        color: #d1d5db;
    }

    .alkon-absen-btn {
        background: white;
        color: var(--alkon-green);
        padding: .6rem 1.4rem;
        border-radius: 999px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* PANELS */
    .alkon-panel {
        background: white;
        border: 1px solid var(--alkon-border);
        border-radius: 16px;
    }

    .alkon-panel-header {
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .alkon-panel-header h3 {
        font-weight: 600;
        color: var(--alkon-text);
    }

    .alkon-status-text {
        font-size: .85rem;
        margin-top: 2px;
        display: block;
    }

    .alkon-panel-body {
        border-top: 1px solid var(--alkon-border);
        padding: 20px 24px;
        background: #fafafa;
    }

    /* TOOLS */
    .alkon-tool {
        background: white;
        border: 1px solid var(--alkon-border);
        border-radius: 14px;
        padding: 22px;
        transition: .2s;
    }

    .alkon-tool h4 {
        font-weight: 600;
        margin-bottom: 4px;
        color: var(--alkon-text);
    }

    .alkon-tool p {
        font-size: .9rem;
        color: var(--alkon-muted);
    }

    .alkon-tool:hover {
        border-color: var(--alkon-green);
        transform: translateY(-2px);
    }

    @media (max-width: 640px) {
        .alkon-status {
            flex-direction: column;
            align-items: flex-start;
        }

        .alkon-absen-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>


</x-app-layout>
