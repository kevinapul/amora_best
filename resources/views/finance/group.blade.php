<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Finance – Invoice Group
        </h2>
    </x-slot>

    @php
        // ================= CONTEXT =================
        $isInhouse = $group->isInhouse();

        $selectedCompanyId = $isInhouse ? $group->billing_company_id : request('company_id');

        $selectedCompany = $companies->firstWhere('id', $selectedCompanyId);

        $participants = collect();

        if ($selectedCompanyId && !$isInhouse) {
            $participants = $group->events
                ->flatMap(fn($e) => $e->participants)
                ->where('company_id', $selectedCompanyId);
        }
    @endphp

    <div class="alkon-root py-10">
        <div class="max-w-6xl mx-auto space-y-8">

            {{-- ================= FLASH ================= --}}
            @if (session('success'))
                <div class="alkon-alert success">{{ session('success') }}</div>
            @endif

            @if (session('info'))
                <div class="alkon-alert info">{{ session('info') }}</div>
            @endif

            {{-- ================= GROUP INFO ================= --}}
            <div class="alkon-panel">
                <h3 class="text-lg font-semibold">
                    📋 {{ $group->masterTraining->nama_training }}
                </h3>

                <div class="mt-2 text-sm text-[var(--alkon-muted)] space-y-1">
                    <p>Job Number: <b>{{ $group->job_number ?? '-' }}</b></p>
                    <p>
                        Tipe Training:
                        <span class="uppercase font-semibold text-[var(--alkon-green)]">
                            {{ $group->training_type }}
                        </span>
                    </p>
                </div>
            </div>

            {{-- ================= SELECT COMPANY ================= --}}
            <div class="alkon-panel">
                <form method="GET" action="{{ route('finance.group.show', $group->id) }}">

                    <label class="alkon-label">Perusahaan</label>

                    <select name="company_id" class="alkon-input" {{ $isInhouse ? 'disabled' : '' }}
                        onchange="this.form.submit()">

                        <option value="">-- Pilih Perusahaan --</option>

                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ $selectedCompanyId == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>

                    @if ($isInhouse)
                        <p class="text-xs text-[var(--alkon-muted)] mt-2">
                            Training INHOUSE → invoice hanya untuk perusahaan induk
                        </p>
                    @endif
                </form>
            </div>

            {{-- ================= INVOICE PANEL ================= --}}
            @if ($selectedCompany)
                <div class="alkon-panel space-y-4">
                    <h4 class="font-semibold">
                        🧾 Invoice – {{ $selectedCompany->name }}
                    </h4>

                    @if ($invoice)
                        <div class="flex items-center gap-3">
                            <span
                                class="alkon-badge
                                {{ $invoice->status === 'paid' ? 'green' : ($invoice->status === 'partial' ? 'yellow' : 'gray') }}">
                                {{ strtoupper($invoice->status) }}
                            </span>

                            <a href="{{ route('invoice.show', $invoice->id) }}" target="_blank"
                                class="alkon-btn secondary text-sm">
                                Download Invoice
                            </a>

                        </div>
                    @else
                        <form method="POST" action="{{ route('finance.group.invoice', $group->id) }}">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">

                            <button class="alkon-btn primary">
                                Buat / Buka Invoice
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- ================= PARTICIPANT / PACKAGE PREVIEW ================= --}}
            @if ($selectedCompany)
                <div class="alkon-panel p-0 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3">Deskripsi</th>
                                <th class="px-4 py-3 text-right">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($isInhouse)
                                <tr class="border-b">
                                    <td class="px-4 py-3 font-medium">
                                        Paket Training Inhouse
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        Rp {{ number_format($group->harga_paket, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @else
                                @foreach ($participants as $p)
                                    <tr class="border-b">
                                        <td class="px-4 py-3 font-medium">
                                            {{ $p->nama }} – {{ $p->pivot->jenis_layanan }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            Rp {{ number_format($p->pivot->harga_peserta, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ================= PAYMENT ================= --}}
            @if ($invoice && $invoice->status !== 'paid')
                <div class="alkon-panel">
                    <h4 class="font-semibold mb-3">💰 Pembayaran</h4>

                    <form method="POST" action="{{ route('finance.invoice.pay', $invoice->id) }}">
                        @csrf

                        <label class="alkon-label">Nominal Pembayaran</label>

                        <input type="number" name="amount" min="1" max="{{ $invoice->remainingAmount() }}"
                            class="alkon-input" required>

                        <p class="text-sm text-[var(--alkon-muted)] mt-1">
                            Sisa tagihan:
                            <b>
                                Rp {{ number_format($invoice->remainingAmount(), 0, ',', '.') }}
                            </b>
                        </p>

                        <button class="alkon-btn primary mt-4">
                            Simpan Pembayaran
                        </button>
                    </form>
                </div>
            @endif

            <div></div>
            <a href="{{ route('event-training.group.show', $group->id) }}" class="alkon-btn secondary">
                ← Kembali ke Group
            </a>

        </div>
    </div>

    {{-- ================= STYLE ================= --}}
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
            min-height: 100vh
        }

        .alkon-panel,
        .alkon-card {
            background: #fff;
            border: 1px solid var(--alkon-border);
            border-radius: 16px;
            padding: 22px
        }

        .alkon-muted {
            color: var(--alkon-muted);
            font-size: .85rem
        }

        .alkon-number {
            font-size: 1.6rem;
            font-weight: 700
        }

        .alkon-label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block
        }

        .alkon-input {
            width: 100%;
            border: 1px solid var(--alkon-border);
            border-radius: 12px;
            padding: 12px 14px
        }

        .alkon-btn {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600
        }

        .alkon-btn.primary {
            background: #4f46e5;
            color: #fff
        }

        .alkon-btn.secondary {
            background: #6b7280;
            color: #fff
        }

        .alkon-badge {
            padding: 4px 12px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600
        }

        .alkon-badge.green {
            background: #dcfce7;
            color: #166534
        }

        .alkon-badge.yellow {
            background: #fef3c7;
            color: #92400e
        }

        .alkon-badge.gray {
            background: #e5e7eb;
            color: #374151
        }

        .alkon-alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: .9rem
        }

        .alkon-alert.success {
            background: #dcfce7;
            color: #166534
        }

        .alkon-alert.info {
            background: #e0f2fe;
            color: #075985
        }
    </style>

</x-app-layout>
