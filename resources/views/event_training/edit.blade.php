<x-app-layout>

<div class="alkon-root py-10">
<div class="max-w-4xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="alkon-status flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold">
                Edit Event
            </h2>
            <p class="text-sm text-gray-200">
                Ubah detail event sebelum approval
            </p>
        </div>

        <a href="{{ route('event-training.index') }}"
           class="alkon-btn-secondary text-xs bg-white/20 text-white hover:bg-white/30 border border-white/30">
            ← Kembali
        </a>
    </div>

    {{-- WARNING IF NOT PENDING --}}
    @if($event->status !== 'pending')
        <div class="alkon-panel">
            <div class="alkon-panel-body text-red-600 font-semibold">
                Event ini sudah di-ACC dan tidak dapat diedit.
            </div>
        </div>
    @endif

    {{-- FORM --}}
    <div class="alkon-panel">
        <div class="alkon-panel-body">

            <form action="{{ route('event-training.update', $event) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- JENIS EVENT --}}
                <div class="mb-4">
                    <label class="font-semibold">Jenis Event</label>
                    <select name="jenis_event"
                            class="alkon-input mt-1"
                            {{ $event->status !== 'pending' ? 'disabled' : '' }}>
                        <option value="training"
                            {{ $event->jenis_event === 'training' ? 'selected' : '' }}>
                            Training
                        </option>
                        <option value="non_training"
                            {{ $event->jenis_event === 'non_training' ? 'selected' : '' }}>
                            Non Training
                        </option>
                    </select>
                </div>

                {{-- TANGGAL --}}
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label>Hari Mulai</label>
                        <input type="number" name="start_day"
                               value="{{ $event->tanggal_start->day }}"
                               class="alkon-input"
                               {{ $event->status !== 'pending' ? 'disabled' : '' }}>
                    </div>

                    <div>
                        <label>Bulan Mulai</label>
                        <input type="number" name="start_month"
                               value="{{ $event->tanggal_start->month }}"
                               class="alkon-input"
                               {{ $event->status !== 'pending' ? 'disabled' : '' }}>
                    </div>

                    <div>
                        <label>Tahun Mulai</label>
                        <input type="number" name="start_year"
                               value="{{ $event->tanggal_start->year }}"
                               class="alkon-input"
                               {{ $event->status !== 'pending' ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div>
                        <label>Hari Selesai</label>
                        <input type="number" name="end_day"
                               value="{{ optional($event->tanggal_end)->day }}"
                               class="alkon-input"
                               {{ $event->status !== 'pending' ? 'disabled' : '' }}>
                    </div>

                    <div>
                        <label>Bulan Selesai</label>
                        <input type="number" name="end_month"
                               value="{{ optional($event->tanggal_end)->month }}"
                               class="alkon-input"
                               {{ $event->status !== 'pending' ? 'disabled' : '' }}>
                    </div>

                    <div>
                        <label>Tahun Selesai</label>
                        <input type="number" name="end_year"
                               value="{{ optional($event->tanggal_end)->year }}"
                               class="alkon-input"
                               {{ $event->status !== 'pending' ? 'disabled' : '' }}>
                    </div>
                </div>

                {{-- ACTION --}}
                @if($event->status === 'pending')
                <div class="flex justify-end gap-2">
                    <button type="submit"
                            class="alkon-btn-primary">
                        Update Event
                    </button>
                </div>
                @endif

            </form>

        </div>
    </div>

</div>
</div>

</x-app-layout>