<x-app-layout>
<div class="max-w-6xl mx-auto py-10 space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="bg-white border rounded-xl p-6 shadow-sm">
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

        {{-- ================= INFO TRAINING ================= --}}
        @if($event->isInhouse())
        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <p class="text-lg font-semibold text-green-800">
                Training INHOUSE
            </p>
            <p class="mt-1 text-green-700">
                Harga Paket:
                <strong class="text-xl">
                    Rp {{ number_format($event->eventTrainingGroup->harga_paket, 0, ',', '.') }}
                </strong>
            </p>
            <p class="text-sm text-green-600 mt-1">
                Harga tidak dihitung per peserta
            </p>
        </div>
        @endif

        {{-- ================= PERUSAHAAN ================= --}}
        <div class="bg-white border rounded-xl p-6 shadow-sm">
            <label class="block text-lg font-medium mb-2">
                Nama Perusahaan (opsional)
            </label>
            <input type="text"
                   name="perusahaan"
                   class="w-full border rounded-lg p-3 text-lg"
                   placeholder="Contoh: PT ABC Indonesia">
        </div>

        {{-- ================= PESERTA ================= --}}
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <p class="text-lg font-semibold text-gray-800">
                    Daftar Peserta
                </p>
                <p class="text-sm text-gray-600">
                    Setiap peserta dapat memiliki jenis layanan dan harga berbeda
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-base">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 border">Nama</th>
                            <th class="p-3 border">No HP</th>
                            <th class="p-3 border">NIK</th>
                            <th class="p-3 border">Jenis Layanan</th>

                            @if($event->isReguler())
                                <th class="p-3 border text-right">Harga (Rp)</th>
                            @endif

                            <th class="p-3 border"></th>
                        </tr>
                    </thead>

                    <tbody id="participantRows">
                        <tr>
                            <td class="border p-2">
                                <input name="participants[0][nama]"
                                       class="w-full p-2 text-lg"
                                       required>
                            </td>
                            <td class="border p-2">
                                <input name="participants[0][no_hp]"
                                       class="w-full p-2 text-lg">
                            </td>
                            <td class="border p-2">
                                <input name="participants[0][nik]"
                                       class="w-full p-2 text-lg">
                            </td>

                            <td class="border p-2">
                                <select name="participants[0][jenis_layanan]"
                                        class="w-full p-2 text-lg border rounded"
                                        required>
                                    <option value="">-- Pilih --</option>
                                    <option value="pelatihan">Pelatihan</option>
                                    <option value="pelatihan_sertifikasi">
                                        Pelatihan + Sertifikasi
                                    </option>
                                    <option value="sertifikasi_resertifikasi">
                                        Sertifikasi / Resertifikasi
                                    </option>
                                </select>
                            </td>

                            @if($event->isReguler())
                            <td class="border p-2">
                                <input type="number"
                                       name="participants[0][harga_peserta]"
                                       class="w-full p-2 text-lg text-right harga-input"
                                       min="0"
                                       oninput="updateTotal()"
                                       required>
                            </td>
                            @endif

                            <td class="border"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 flex gap-3">
                <button type="button"
                        onclick="addParticipant()"
                        class="px-4 py-2 bg-gray-200 rounded-lg text-base">
                    + Tambah Peserta
                </button>
            </div>
        </div>

        {{-- ================= TOTAL ================= --}}
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
        <div class="flex justify-end pt-4">
            <button type="submit"
                    class="px-8 py-3 bg-indigo-600 text-white rounded-xl
                           text-lg font-semibold">
                Simpan Peserta
            </button>
        </div>
    </form>
</div>

<script>
let index = 1;

function formatRupiah(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.harga-input').forEach(input => {
        total += parseInt(input.value || 0);
    });
    const el = document.getElementById('totalHarga');
    if (el) el.innerText = formatRupiah(total);
}

function addParticipant() {
    const tbody = document.getElementById('participantRows');
    const tr = document.createElement('tr');

    tr.innerHTML = `
        <td class="border p-2">
            <input name="participants[${index}][nama]"
                   class="w-full p-2 text-lg" required>
        </td>

        <td class="border p-2">
            <input name="participants[${index}][no_hp]"
                   class="w-full p-2 text-lg">
        </td>

        <td class="border p-2">
            <input name="participants[${index}][nik]"
                   class="w-full p-2 text-lg">
        </td>

        <td class="border p-2">
            <select name="participants[${index}][jenis_layanan]"
                    class="w-full p-2 text-lg border rounded"
                    required>
                <option value="">-- Pilih --</option>
                <option value="pelatihan">Pelatihan</option>
                <option value="pelatihan_sertifikasi">
                    Pelatihan + Sertifikasi
                </option>
                <option value="sertifikasi_resertifikasi">
                    Sertifikasi / Resertifikasi
                </option>
            </select>
        </td>

        @if($event->isReguler())
        <td class="border p-2">
            <input type="number"
                   name="participants[${index}][harga_peserta]"
                   class="w-full p-2 text-lg text-right harga-input"
                   min="0"
                   oninput="updateTotal()"
                   required>
        </td>
        @endif

        <td class="border text-center">
            <button type="button"
                    onclick="this.closest('tr').remove(); updateTotal();"
                    class="text-red-600 text-xl font-bold">
                ×
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    index++;
}
</script>
</x-app-layout>
