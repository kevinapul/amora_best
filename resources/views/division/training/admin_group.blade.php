<x-app-layout>
    <div class="alkon-root py-10">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- HEADER --}}
            <div class="alkon-status">
                <div>
                    <h2 class="text-xl font-semibold">
                        {{ $group->masterTraining->nama_training }} — {{ $group->job_number }}
                    </h2>
                    <p class="text-sm text-gray-200">
                        Administrasi Sertifikat Peserta
                    </p>
                </div>
                <a href="{{ route('division.training') }}"
                    class="alkon-btn-secondary text-xs bg-white/20 text-white hover:bg-white/30 border border-white/30">
                    ← Administrasi Sertifikat
                </a>
            </div>

            {{-- PILIH PERUSAHAAN --}}
            <div class="alkon-panel">
                <div class="alkon-panel-header">
                    Pilih Perusahaan
                </div>

                <div class="alkon-panel-body">
                    <form method="GET">
                        <select name="company_id" onchange="this.form.submit()" class="alkon-input w-96">
                            <option value="">-- pilih perusahaan --</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c->id }}" {{ $selectedCompany == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            {{-- INFO PEMBAYARAN --}}
            @if ($invoice)
                @php
                    $percent = $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0;
                @endphp

                <div class="alkon-panel">
                    <div class="alkon-panel-header">
                        Status Pembayaran
                    </div>

                    <div class="alkon-panel-body grid grid-cols-3 gap-6 text-sm">

                        <div>
                            <div class="text-gray-500">Total</div>
                            <div class="text-lg font-semibold">
                                Rp {{ number_format($invoice->total_amount) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">Paid</div>
                            <div class="text-lg font-semibold text-green-600">
                                Rp {{ number_format($invoice->paid_amount) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">Progress</div>
                            <div class="text-lg font-semibold">
                                {{ round($percent) }}%
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            {{-- LIST PESERTA --}}
            @if ($selectedCompany)
                <div class="alkon-panel">
                    <div class="alkon-panel-header">
                        Daftar Peserta
                    </div>

                    <div class="alkon-panel-body overflow-x-auto">

                        <table class="w-full text-sm">
                            <thead class="border-b">
                                <tr class="text-gray-600">
                                    <th class="py-3 text-left">Peserta</th>
                                    <th>Training</th>
                                    <th>No Sertifikat</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($group->events as $event)
                                    @foreach ($event->participants->where('pivot.company_id', $selectedCompany) as $p)
                                        @php
                                            $cert = $p->certificates->where('event_training_id', $event->id)->first();
                                            $allow = $invoice && $invoice->paid_amount / $invoice->total_amount >= 0.5;
                                        @endphp

                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 font-medium">{{ $p->nama }}</td>
                                            <td>{{ $event->training->name }}</td>
                                            <td>{{ $cert->nomor_sertifikat ?? '-' }}</td>

                                            <td class="text-right">

                                                @php
                                                    $cert = $p->certificates
                                                        ->where('event_training_id', $event->id)
                                                        ->first();
                                                    $allow =
                                                        $invoice &&
                                                        $invoice->paid_amount / $invoice->total_amount >= 0.5;
                                                @endphp

                                                @if (!$allow)
                                                    <span class="text-red-500 text-xs font-semibold">
                                                        🔒 Belum 50% bayar
                                                    </span>
                                                @elseif(!$cert)
                                                    <a href="{{ route('certificate.detail', [
                                                        'participant' => $p->id,
                                                        'event' => $event->id,
                                                    ]) }}"
                                                        class="alkon-btn-primary text-xs">
                                                        Buat Sertifikat
                                                    </a>
                                                @else
                                                    <a href="{{ route('certificate.detail', [
                                                        'participant' => $p->id,
                                                        'event' => $event->id,
                                                    ]) }}"
                                                        class="alkon-btn-secondary text-xs">
                                                        Detail
                                                    </a>
                                                @endif

                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach

                            </tbody>
                        </table>

                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
