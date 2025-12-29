<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Peserta Event – {{ $event->training->name }} ({{ $event->job_number }})
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto">

        <!-- Tombol Tambah Peserta -->
        <div class="mb-4">
            <a href="{{ route('event-participant.create', $event->id) }}"
               class="px-4 py-2 bg-green-600 text-white rounded">
               Tambah Peserta
            </a>
        </div>

        <table class="w-full bg-white border rounded">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 border">Nama</th>
                    <th class="p-3 border">Perusahaan</th>
                    <th class="p-3 border">No HP</th>
                    <th class="p-3 border text-center">Harga</th>
                    <th class="p-3 border text-center">Paid</th>
                    <th class="p-3 border text-center">Sertifikat</th>
                    <th class="p-3 border text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($event->participants as $p)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">{{ $p->nama }}</td>
                        <td class="p-3">{{ $p->perusahaan ?? '-' }}</td>
                        <td class="p-3">{{ $p->no_hp ?? '-' }}</td>
                        <td class="p-3 text-center">
                            {{ $p->pivot->harga_peserta }}
                        </td>
                        <td class="p-3 text-center">
                            @if($p->pivot->is_paid)
                                <span class="text-green-700 font-bold">✔</span>
                                <br>
                                <small>{{ $p->pivot->paid_at?->format('d M Y') }}</small>
                            @else
                                <span class="text-red-600 font-bold">✖</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($p->pivot->certificate_ready)
                                <span class="text-green-700 font-bold">✔</span>
                                <br>
                                <small>{{ $p->pivot->certificate_issued_at?->format('d M Y') }}</small>
                            @else
                                <span class="text-yellow-600 font-bold">⏳</span>
                            @endif
                        </td>
                        <td class="p-3 text-center space-x-1">
                            <!-- Tombol Mark Paid -->
                            @if(!$p->pivot->is_paid)
                                <form action="{{ route('event-participant.markPaid', [$event->id, $p->id]) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-2 py-1 bg-blue-600 text-white rounded text-sm">
                                        Mark Paid
                                    </button>
                                </form>
                            @endif

                            <!-- Tombol Generate Certificate -->
                            @if($p->pivot->is_paid && !$p->pivot->certificate_ready && $event->canGenerateCertificate())
                                <form action="{{ route('event-participant.generateCertificate', [$event->id, $p->id]) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-2 py-1 bg-green-600 text-white rounded text-sm">
                                        Generate Cert
                                    </button>
                                </form>
                            @endif

                            <!-- Tombol Hapus Peserta -->
                            <form action="{{ route('event-participant.destroy', [$event->id, $p->id]) }}"
                                  method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-2 py-1 bg-red-600 text-white rounded text-sm">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</x-app-layout>
