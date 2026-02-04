<x-app-layout>
<div class="alkon-root py-10">
<div class="max-w-6xl mx-auto space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="bg-white border rounded-2xl p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-gray-800">
            Tambah Peserta Training
        </h1>
        <p class="mt-1 text-gray-600">
            {{ $event->training->code }} — {{ $event->training->name }}
        </p>
        <p class="text-sm text-gray-500">
            Tanggal: {{ optional($event->tanggal_start)->format('d M Y') }}
        </p>
    </div>

    <form action="{{ route('event-participant.store', $event) }}" method="POST">
        @csrf

        {{-- ================= INHOUSE INFO ================= --}}
        @if($event->isInhouse())
        <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
            <p class="font-semibold text-green-800 text-lg">
                🏢 Training INHOUSE
            </p>
            <p class="mt-1 text-green-700">
                Harga Paket:
                <strong class="text-xl">
                    Rp {{ number_format($event->eventTrainingGroup->harga_paket,0,',','.') }}
                </strong>
            </p>
            <p class="text-sm text-green-600">
                Harga tidak dihitung per peserta
            </p>
        </div>
        @endif

        {{-- ================= PERUSAHAAN ================= --}}
        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <label class="block font-semibold text-gray-800 mb-1">
                🏢 Nama Perusahaan
            </label>
            <p class="text-sm text-gray-500 mb-3">
                Kosongkan jika peserta individu
            </p>
            <input type="text"
                   name="perusahaan"
                   class="alkon-input w-full"
                   placeholder="Contoh: PT ABC Indonesia">
        </div>

        {{-- ================= PESERTA ================= --}}
        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">
                    📋 Daftar Peserta
                </h3>
                <p class="text-sm text-gray-500">
                    Tambahkan satu atau lebih peserta training
                </p>
            </div>

            <div id="participantRows" class="p-6 space-y-5">

                {{-- PESERTA 1 --}}
                <div class="participant-card p-5 rounded-xl">
                    <p class="font-semibold text-gray-700 mb-3">
                        👤 Peserta 1
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="alkon-label">Nama Peserta *</label>
                            <input name="participants[0][nama]"
                                   class="alkon-input" required>
                        </div>

                        <div>
                            <label class="alkon-label">No HP</label>
                            <input name="participants[0][no_hp]"
                                   class="alkon-input">
                        </div>

                        <div>
                            <label class="alkon-label">NIK</label>
                            <input name="participants[0][nik]"
                                   class="alkon-input">
                        </div>

                        <div>
                            <label class="alkon-label">Jenis Layanan *</label>
                            <select name="participants[0][jenis_layanan]"
                                    class="alkon-input" required>
                                <option value="">-- Pilih --</option>
                                <option value="pelatihan">Pelatihan</option>
                                <option value="pelatihan_sertifikasi">
                                    Pelatihan + Sertifikasi
                                </option>
                                <option value="sertifikasi_resertifikasi">
                                    Sertifikasi / Resertifikasi
                                </option>
                            </select>
                        </div>

                        @if($event->isReguler())
                        <div class="col-span-2">
                            <label class="alkon-label">Harga Peserta *</label>
                            <input type="number"
                                   name="participants[0][harga_peserta]"
                                   class="alkon-input text-right harga-input"
                                   oninput="updateTotal()"
                                   min="0" required>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            <div class="px-6 pb-6">
                <button type="button"
                        onclick="addParticipant()"
                        class="border border-dashed border-gray-400
                               rounded-xl px-5 py-2
                               text-gray-700 hover:bg-gray-100">
                    + Tambah Peserta
                </button>
            </div>
        </div>

        {{-- ================= TOTAL REGULER ================= --}}
        @if($event->isReguler())
        <div class="flex justify-end">
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-6 py-4">
                <p class="text-sm text-indigo-700">
                    Total Harga
                </p>
                <p class="text-2xl font-bold text-indigo-800">
                    Rp <span id="totalHarga">0</span>
                </p>
            </div>
        </div>
        @endif

        {{-- ================= SUBMIT ================= --}}
        <div class="flex justify-end">
            <button type="submit"
                    class="px-8 py-3 bg-indigo-600 text-white
                           rounded-xl text-lg font-semibold">
                Simpan Peserta
            </button>
        </div>

    </form>
</div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
let index = 1;

function formatRupiah(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.harga-input').forEach(i => {
        total += parseInt(i.value || 0);
    });
    const el = document.getElementById('totalHarga');
    if (el) el.innerText = formatRupiah(total);
}

function addParticipant() {
    const container = document.getElementById('participantRows');

    const div = document.createElement('div');
    div.className = 'participant-card p-5 rounded-xl';

    div.innerHTML = `
        <p class="font-semibold text-gray-700 mb-3">
            👤 Peserta ${index + 1}
        </p>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="alkon-label">Nama Peserta *</label>
                <input name="participants[${index}][nama]" class="alkon-input" required>
            </div>

            <div>
                <label class="alkon-label">No HP</label>
                <input name="participants[${index}][no_hp]" class="alkon-input">
            </div>

            <div>
                <label class="alkon-label">NIK</label>
                <input name="participants[${index}][nik]" class="alkon-input">
            </div>

            <div>
                <label class="alkon-label">Jenis Layanan *</label>
                <select name="participants[${index}][jenis_layanan]"
                        class="alkon-input" required>
                    <option value="">-- Pilih --</option>
                    <option value="pelatihan">Pelatihan</option>
                    <option value="pelatihan_sertifikasi">Pelatihan + Sertifikasi</option>
                    <option value="sertifikasi_resertifikasi">Sertifikasi / Resertifikasi</option>
                </select>
            </div>

            @if($event->isReguler())
            <div class="col-span-2">
                <label class="alkon-label">Harga Peserta *</label>
                <input type="number"
                       name="participants[${index}][harga_peserta]"
                       class="alkon-input text-right harga-input"
                       oninput="updateTotal()" min="0" required>
            </div>
            @endif
        </div>

        <button type="button"
                onclick="this.parentElement.remove(); updateTotal();"
                class="mt-3 text-red-600 text-sm">
            Hapus Peserta
        </button>
    `;

    container.appendChild(div);
    index++;
}
</script>

<style>
.alkon-root {
    background: #f5f7f6;
}

.alkon-input {
    background: white;
    border: 1px solid #d1d5db;
    padding: .75rem;
    border-radius: .75rem;
    width: 100%;
}

.alkon-input:focus {
    border-color: #0f3d2e;
    box-shadow: 0 0 0 2px rgba(15,61,46,.15);
    outline: none;
}

.alkon-label {
    font-size: .85rem;
    color: #374151;
    margin-bottom: .25rem;
    display: block;
}

.participant-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
}
</style>
</x-app-layout>
