<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">

        <h2 class="text-xl font-semibold mb-6">📊 Dashboard HR</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Sedang Aktif -->
            <div class="bg-white p-6 rounded shadow-md">
                <h3 class="text-lg font-semibold mb-4">👨‍💼 Sedang Aktif</h3>
                <table class="w-full text-left border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">Nama</th>
                            <th class="p-2 border">Jam Masuk</th>
                            <th class="p-2 border">Durasi</th>
                            <th class="p-2 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeUsers as $user)
                        <tr class="border-b">
                            <td class="p-2">{{ $user->name }}</td>
                            <td class="p-2">{{ $user->check_in->format('H:i') }}</td>
                            <td class="p-2">
                                <span class="timer" data-start="{{ $user->check_in->timestamp }}">--:--:--</span>
                            </td>
                            <td class="p-2">
                                <form method="POST" action="{{ route('hr.forceCheckout', $user->id) }}">
                                    @csrf
                                    <button class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                                        Force Checkout
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Belum Absen -->
            <div class="bg-white p-6 rounded shadow-md">
                <h3 class="text-lg font-semibold mb-4">⏳ Belum Absen</h3>
                <table class="w-full text-left border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">Nama</th>
                            <th class="p-2 border">Divisi</th>
                            <th class="p-2 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingUsers as $user)
                        <tr class="border-b">
                            <td class="p-2">{{ $user->name }}</td>
                            <td class="p-2">{{ $user->division }}</td>
                            <td class="p-2">
                                <button class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600"
                                    onclick="alert('Reminder dikirim ke {{ $user->name }}')">
                                    Kirim Reminder
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

<script>
    // Timer real-time untuk "Sedang Aktif"
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
