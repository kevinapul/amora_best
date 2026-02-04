<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Finance – Pembayaran Event
        </h2>
    </x-slot>

@php
    $isInhouse = $event->isInhouse();

    if ($isInhouse) {
        $group = $event->eventTrainingGroup;

        $totalTagihan = $group->totalTagihan();
        $totalPaid    = $group->totalLunas();
        $sisa         = $group->sisaTagihan();
    } else {
        $totalTagihan = $event->participants
            ->sum(fn($p) => $p->pivot->harga_peserta);

        $totalPaid = $event->participants
            ->sum(fn($p) => $p->pivot->paid_amount ?? 0);

        $sisa = max(0, $totalTagihan - $totalPaid);
    }
@endphp
    

    <div class="alkon-root py-10">
        <div class="max-w-6xl mx-auto space-y-8">

            {{-- ================= SUMMARY ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="alkon-card">
                    <p class="alkon-muted">Total Tagihan</p>
                    <p class="alkon-number text-indigo-700">
                        Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                    </p>
                </div>

                <div class="alkon-card">
                    <p class="alkon-muted">Sudah Dibayar</p>
                    <p class="alkon-number text-green-600">
                        Rp {{ number_format($totalPaid, 0, ',', '.') }}
                    </p>
                </div>

                <div class="alkon-card">
                    <p class="alkon-muted">Sisa Tagihan</p>
                    <p class="alkon-number text-red-600">
                        Rp {{ number_format($sisa, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('event-training.bulk-payment', $event->id) }}" id="financeForm">
                @csrf

                {{-- ================= INHOUSE NOTICE ================= --}}
                @if ($isInhouse)
                    <div class="alkon-panel bg-green-50 border-green-200">
                        <h3 class="font-semibold text-green-800 text-lg">
                            Training INHOUSE
                        </h3>
                        <p class="text-sm text-green-700 mt-1">
                            Pembayaran dilakukan berdasarkan <b>harga paket</b>.
                            Pembayaran boleh <b>cicilan</b>.
                            Finance hanya dapat approve jika total pembayaran
                            telah mencapai harga paket.
                        </p>
                    </div>
                @endif

                {{-- ================= COMPANY SELECT (REGULER ONLY) ================= --}}
                @if (!$isInhouse)
                    <div class="alkon-panel">
                        <label class="alkon-label">Perusahaan / Individu *</label>
                        <select name="company" id="companySelect" class="alkon-input" required>
                            <option value="">-- Pilih --</option>
                            <option value="INDIVIDU">INDIVIDU</option>

                            @foreach ($companies as $company => $items)
                                @if ($company !== 'INDIVIDU')
                                    @php
                                        $total = $items->sum(fn($p) => $p->pivot->harga_peserta);
                                        $paid = $items->sum(fn($p) => $p->pivot->paid_amount ?? 0);
                                    @endphp
                                    <option value="{{ $company }}">
                                        {{ $company }}
                                        — {{ number_format($paid, 0, ',', '.') }}
                                        / {{ number_format($total, 0, ',', '.') }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- ================= PARTICIPANTS (REGULER ONLY) ================= --}}
                @if (!$isInhouse)
                    <div class="alkon-panel">
                        <table class="alkon-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Nama</th>
                                    <th>Perusahaan</th>
                                    <th class="text-right">Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($event->participants as $p)
                                    <tr class="participant-row" data-company="{{ $p->perusahaan ?? 'INDIVIDU' }}">
                                        <td>
                                            <input type="checkbox" name="participants[]" value="{{ $p->id }}"
                                                class="participant-check hidden">
                                        </td>
                                        <td>{{ $p->nama }}</td>
                                        <td>{{ $p->perusahaan ?? 'INDIVIDU' }}</td>
                                        <td class="text-left">
                                            Rp {{ number_format($p->pivot->harga_peserta, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            @if (($p->pivot->remaining_amount ?? $p->pivot->harga_peserta) <= 0)
                                                <span class="alkon-badge green">LUNAS</span>
                                            @else
                                                <span class="alkon-badge red">BELUM</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>  
                @endif

                {{-- ================= AMOUNT ================= --}}
                <div class="alkon-panel">
                    <label class="alkon-label">Jumlah Dibayarkan *</label>

                    <div class="alkon-input-group">
                        <span class="alkon-prefix">Rp</span>
                        <input type="text" id="amountInput" name="amount" class="alkon-input alkon-money"
                            placeholder="0" data-max="{{ $sisa }}" inputmode="numeric" required>
                    </div>

                    <p class="alkon-muted text-sm mt-1">
                        Maksimal pembayaran:
                        <b>Rp {{ number_format($sisa, 0, ',', '.') }}</b><br>
                        Pembayaran sebagian (cicilan) diperbolehkan.
                    </p>
                </div>

                {{-- ================= ACTION ================= --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('event-training.show', $event->id) }}" class="alkon-btn secondary">
                        Batal
                    </a>

                    <button type="submit" class="alkon-btn primary">
                        Simpan Pembayaran
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ================= SCRIPT ================= --}}
    <script>
        const amountInput = document.getElementById('amountInput');
        const maxAmount = parseInt(amountInput?.dataset.max || 0);

        function formatRupiah(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // FORMAT + HARD LIMIT
        if (amountInput) {
            amountInput.addEventListener('input', () => {
                let raw = amountInput.value.replace(/\D/g, '');
                if (!raw) {
                    amountInput.value = '';
                    return;
                }

                let value = parseInt(raw);
                if (value > maxAmount) value = maxAmount;

                amountInput.value = formatRupiah(value);
            });
        }

        // sebelum submit → kirim angka murni
        document.getElementById('financeForm')
            .addEventListener('submit', () => {
                amountInput.value = amountInput.value.replace(/\./g, '');
            });

        // FILTER PARTICIPANT (REGULER)
        const companySelect = document.getElementById('companySelect');
        const rows = document.querySelectorAll('.participant-row');

        if (companySelect) {
            companySelect.addEventListener('change', () => {
                const val = companySelect.value;

                rows.forEach(row => {
                    const chk = row.querySelector('.participant-check');

                    if (!val) {
                        row.classList.add('hidden');
                        chk.classList.add('hidden');
                        chk.checked = false;
                        return;
                    }

                    if (row.dataset.company === val) {
                        row.classList.remove('hidden');

                        if (val === 'INDIVIDU') {
                            chk.classList.remove('hidden');
                        } else {
                            chk.classList.add('hidden');
                            chk.checked = false;
                        }
                    } else {
                        row.classList.add('hidden');
                        chk.classList.add('hidden');
                        chk.checked = false;
                    }
                });
            });
        }
    </script>

    <style>
        .alkon-root {
            background: #f5f7f6
        }

        .alkon-card,
        .alkon-panel {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 22px;
        }

        .alkon-muted {
            color: #6b7280;
            font-size: .85rem
        }

        .alkon-number {
            font-size: 1.5rem;
            font-weight: 700
        }

        .alkon-label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block
        }

        .alkon-input-group {
            position: relative
        }

        .alkon-prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-weight: 600;
            pointer-events: none;
            /* ⬅️ PENTING */
        }

        .alkon-money {
            text-indent: 42px;
            /* ⬅️ CARET DIPAKSA LEWAT Rp */
        }


        .alkon-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 12px 14px;
        }

        .alkon-btn {
            padding: 12px 22px;
            border-radius: 12px;
            font-weight: 600;
        }

        .alkon-btn.primary {
            background: #4f46e5;
            color: white
        }

        .alkon-btn.secondary {
            background: #6b7280;
            color: white
        }

        .alkon-table {
            width: 100%;
            font-size: .9rem
        }

        .alkon-table th {
            text-align: left;
            padding: 12px
        }

        .alkon-table td {
            padding: 12px;
            border-top: 1px solid #eee
        }

        .alkon-badge {
            padding: 4px 12px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
        }

        .alkon-badge.green {
            background: #dcfce7;
            color: #166534
        }

        .alkon-badge.red {
            background: #fee2e2;
            color: #991b1b
        }
    </style>
</x-app-layout>
