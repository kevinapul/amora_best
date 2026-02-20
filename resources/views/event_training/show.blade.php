<x-app-layout>

    @php
        try {
            $start = $event->tanggal_start
                ? \Carbon\Carbon::parse($event->tanggal_start)->translatedFormat('d F Y')
                : null;

            $end = $event->tanggal_end ? \Carbon\Carbon::parse($event->tanggal_end)->translatedFormat('d F Y') : null;

            $tanggal = $start && $end ? ($start === $end ? $start : "$start – $end") : '-';
        } catch (\Exception $ex) {
            $tanggal = '-';
        }

        $group = $event->eventTrainingGroup;

    @endphp

    <div class="max-w-7xl mx-auto px-6 py-8">


        <!-- ================= HEADER ================= -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">

            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">

                <div>
                    <h1 class="text-2xl font-bold text-gray-800 tracking-wide">
                        {{ $event->training->name }}
                        <span class="text-sm text-gray-400 font-normal">
                            ({{ $event->training->code }})
                        </span>
                    </h1>

                    <p class="mt-3 text-sm text-gray-500">
                        Job Number:
                        <span class="font-semibold text-gray-700">
                            {{ $group->job_number ?? '-' }}
                        </span>
                    </p>
                </div>

                <div>
                    <span
                        class="px-4 py-2 rounded-full text-xs font-bold tracking-wide
                {{ $event->status === 'done'
                    ? 'bg-green-100 text-green-700'
                    : ($event->status === 'active'
                        ? 'bg-blue-100 text-blue-700'
                        : ($event->status === 'on_progress'
                            ? 'bg-yellow-100 text-yellow-700'
                            : 'bg-gray-200 text-gray-700')) }}">
                        {{ strtoupper($event->status) }}
                    </span>
                </div>

            </div>
        </div>

        <!-- ================= INFO GRID ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-400">Tanggal</p>
                <p class="font-semibold text-gray-800 mt-1">{{ $tanggal }}</p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-400">Tempat</p>
                <p class="font-semibold text-gray-800 mt-1">{{ $group->tempat ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-400">Sertifikasi</p>
                <p class="font-semibold text-gray-800 mt-1">{{ $group->sertifikasi ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-400">Instruktur</p>
                <p class="font-semibold text-gray-800 mt-1">
                    {{ $staffs['Instruktur'] ?? '-' }}
                </p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-400">Training Officer</p>
                <p class="font-semibold text-gray-800 mt-1">
                    {{ $staffs['Training Officer'] ?? '-' }}
                </p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-400">Jenis Event</p>
                <p class="font-semibold text-gray-800 mt-1">
                    {{ ucfirst($event->jenis_event) }}
                    @if ($event->jenis_event === 'training')
                        ({{ ucfirst($group->training_type) }})
                    @elseif ($event->jenis_event === 'non_training')
                        ({{ ucfirst($event->non_training_type) }})
                    @endif
                </p>
            </div>

        </div>

        <!-- ================= ACTION ================= -->
        <div class="flex flex-wrap gap-3 mb-8">

            @can('addParticipant', $event)
                <a href="{{ route('event-participant.create', $event->id) }}"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                    + Tambah Peserta
                </a>
            @endcan

            @can('addInstructor', $event)
                <a href="{{ route('event-staff.create', $event->id) }}"
                    class="px-5 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                    + Tambah Instruktur
                </a>
            @endcan

        </div>

        <!-- ================= PESERTA ================= -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-700">
                    👥 Daftar Peserta
                </h3>
            </div>

            @if ($event->participants->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3 text-center w-12">No</th>
                                <th class="px-6 py-3 text-left">Nama</th>
                                <th class="px-6 py-3 text-left">Perusahaan</th>
                                <th class="px-6 py-3 text-center">No HP</th>
                                <th class="px-6 py-3 text-center">Biaya</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach ($event->participants as $i => $participant)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-center text-gray-500">
                                        {{ $i + 1 }}
                                    </td>

                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        {{ $participant->nama }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $participant->pivot->company_id ? \App\Models\Company::find($participant->pivot->company_id)?->name : '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-center text-gray-700">
                                        {{ $participant->no_hp ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-center font-bold text-gray-800">
                                        Rp {{ number_format($participant->pivot->harga_peserta ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-10 text-center text-gray-400">
                    Belum ada peserta
                </div>
            @endif

        </div>

        <!-- ================= BACK ================= -->
        <div class="mt-8">
            <a href="{{ route('event-training.group.show', $group->id) }}"
                class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">
                ← Kembali ke Group
            </a>
        </div>


    </div>

</x-app-layout>
