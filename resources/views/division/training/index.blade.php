<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Administrasi Sertifikat Training
        </h2>

    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6">

        @forelse ($events as $event)
            <div class="bg-white shadow rounded-lg overflow-hidden">

                {{-- Header Event --}}
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <div class="font-semibold text-gray-800">
                        {{ $event->training->name }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $event->job_number }} |
                        {{ $event->tanggal_start }} - {{ $event->tanggal_end }}
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 border text-left">Nama Peserta</th>
                                <th class="px-4 py-3 border text-center">Status Bayar</th>
                                <th class="px-4 py-3 border text-left">Nomor Sertifikat</th>
                                <th class="px-4 py-3 border text-center">Tanggal Terbit</th>
                                <th class="px-4 py-3 border text-center w-1/4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->participants as $participant)
                                @php
                                    $pivot = $participant->pivot;
                                    $certificate = $participant->certificates
                                        ->where('event_training_id', $event->id)
                                        ->first();
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    {{-- Nama --}}
                                    <td class="px-4 py-3 border">
                                        {{ $participant->nama }}
                                    </td>

                                    {{-- Status Bayar --}}
                                    <td class="px-4 py-3 border text-center">
                                        @if ($event->isInhouse() || $pivot->is_paid)
                                            <span
                                                class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-700 font-semibold">
                                                Lunas
                                            </span>
                                        @else
                                            <span
                                                class="inline-block px-2 py-1 text-xs rounded bg-gray-200 text-gray-600 font-semibold">
                                                Belum
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Nomor Sertifikat --}}
                                    <td class="px-4 py-3 border">
                                        {{ $certificate->nomor_sertifikat ?? '-' }}
                                    </td>

                                    {{-- Tanggal Terbit --}}
                                    <td class="px-4 py-3 border text-center">
                                        {{ $certificate->tanggal_terbit ?? '-' }}
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-4 py-3 border">
                                        @if (!$certificate)
                                            <form
                                                action="{{ route('event-participant.recordCertificate', [
                                                    'event' => $event->id,
                                                    'participant' => $participant->id,
                                                ]) }}"
                                                method="POST"
                                                class="flex items-center gap-2"
                                            >
                                                @csrf

                                                <input
                                                    type="text"
                                                    name="nomor_sertifikat"
                                                    placeholder="Nomor"
                                                    required
                                                    class="w-full px-2 py-1 text-sm border rounded focus:outline-none focus:ring focus:ring-blue-200"
                                                >

                                                <input
                                                    type="date"
                                                    name="tanggal_terbit"
                                                    required
                                                    class="px-2 py-1 text-sm border rounded focus:outline-none focus:ring focus:ring-blue-200"
                                                >

                                                <button
                                                    type="submit"
                                                    class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                                >
                                                    Simpan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-500 italic text-sm">
                                                Sudah dicatat
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @empty
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                Belum ada event training yang selesai.
            </div>
        @endforelse

    </div>
</x-app-layout>
