<x-app-layout>
    <div class="alkon-root py-10">
        <div class="max-w-3xl mx-auto space-y-6">

            {{-- SUCCESS POPUP --}}
            @if (session('success'))
                <div x-data="{ open: true }" x-show="open"
                    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">

                    <div class="bg-white rounded-xl p-6 w-96 shadow-xl text-center space-y-4">
                        <div class="text-green-600 text-lg font-semibold">
                            ✅ {{ session('success') }}
                        </div>
                        <button @click="open=false" class="alkon-btn-primary w-full justify-center">
                            OK
                        </button>
                    </div>
                </div>
            @endif

            {{-- HEADER --}}
            <div class="alkon-status flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold">
                        Detail Sertifikat
                    </h2>
                    <p class="text-sm text-gray-200">
                        {{ $participant->nama }} — {{ $event->training->name }}
                    </p>
                </div>

                <a href="{{ route('division.training') }}" class="alkon-btn-secondary text-xs">
                    ← Kembali
                </a>
            </div>

            {{-- FORM --}}
            <div class="alkon-panel">
                <div class="alkon-panel-body space-y-5">

                    <form method="POST" action="{{ route('certificate.save', [$participant->id, $event->id]) }}"
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="text-sm font-semibold">Nomor Sertifikat</label>
                            <input name="nomor_sertifikat" value="{{ $certificate->nomor_sertifikat }}"
                                class="alkon-input" required>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Tanggal Terbit</label>
                            <input type="date" name="tanggal_terbit"
                                value="{{ optional($certificate->tanggal_terbit)->format('Y-m-d') }}"
                                class="alkon-input" required>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Masa Berlaku (tahun)</label>
                            <input type="number" name="masa_berlaku_tahun"
                                value="{{ $certificate->masa_berlaku_tahun }}" class="alkon-input">
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Upload PDF (optional)</label>
                            <input type="file" name="file" accept="application/pdf" class="alkon-input">
                        </div>

                        @if ($certificate->file_path)
                            <div class="flex gap-4 items-center text-sm mt-3">
                                <a href="{{ asset('storage/' . $certificate->file_path) }}" target="_blank"
                                    class="text-blue-600 underline">
                                    📄 Lihat PDF
                                </a>

                                <a href="{{ asset('storage/' . $certificate->file_path) }}" download
                                    class="text-green-600 underline">
                                    ⬇ Download
                                </a>
                            </div>
                        @endif

                        <button class="alkon-btn-primary w-full justify-center mt-4">
                            Simpan Sertifikat
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
