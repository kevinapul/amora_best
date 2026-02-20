<x-app-layout>
<div class="alkon-root py-10">
<div class="max-w-3xl mx-auto space-y-6">


{{-- HEADER --}}
<div class="alkon-status">
    <div>
        <h2 class="text-xl font-semibold">
            Tambah Instruktur / Training Officer
        </h2>
        <p class="text-sm text-gray-200">
            {{ $event->training->name }}
        </p>
    </div>
</div>

<div class="alkon-panel">
<div class="alkon-panel-body">

    <form action="{{ route('event-staff.store',$event->id) }}" method="POST">
        @csrf

        {{-- ================= INSTRUKTUR ================= --}}
        <div class="mb-6">
            <label class="text-sm font-semibold">
                Instruktur (max 2)
            </label>

            @for($i=0;$i<2;$i++)
                <input type="text"
                    name="instrukturs[]"
                    class="alkon-input mt-2"
                    placeholder="Nama instruktur..."
                    {{ $instrukturCount >= 2 ? 'disabled' : '' }}>
            @endfor

            @if($instrukturCount >= 2)
                <p class="text-xs text-red-500 mt-1">
                    Maksimal instruktur sudah tercapai
                </p>
            @endif
        </div>

        {{-- ================= OFFICER ================= --}}
        <div class="mb-6">
            <label class="text-sm font-semibold">
                Training Officer (max 2)
            </label>

            @for($i=0;$i<2;$i++)
                <input type="text"
                    name="training_officers[]"
                    class="alkon-input mt-2"
                    placeholder="Nama training officer..."
                    {{ $officerCount >= 2 ? 'disabled' : '' }}>
            @endfor

            @if($officerCount >= 2)
                <p class="text-xs text-red-500 mt-1">
                    Maksimal officer sudah tercapai
                </p>
            @endif
        </div>

        {{-- ACTION --}}
        <div class="flex gap-3">
            <button class="alkon-btn-primary"
                {{ ($instrukturCount>=2 && $officerCount>=2)?'disabled':'' }}>
                Simpan
            </button>

            <a href="{{ route('event-staff.show',$event->id) }}"
                class="alkon-btn-secondary">
                Kembali
            </a>
        </div>

    </form>

</div>
</div>


</div>
</div>
</x-app-layout>
