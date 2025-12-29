<x-app-layout>
    @section('content')
    <div class="max-w-3xl mx-auto mt-10 bg-white p-8 shadow-lg rounded-xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-800">Tambah Event Training</h1>

        <form action="{{ route('event-training.store') }}" method="POST">
            @csrf

            {{-- Training --}}
            <div class="mb-4">
                <label class="block font-semibold">Pilih Training</label>
                <select name="training_id" class="w-full border rounded p-2">
                    @foreach($trainings as $training)
                        <option value="{{ $training->id }}">{{ $training->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal --}}
            <div class="mb-4">
                <label class="block font-semibold">Tanggal</label>
                <input type="date" name="tanggal" class="w-full border rounded p-2">
            </div>

            {{-- Tempat --}}
            <div class="mb-4">
                <label class="block font-semibold">Tempat</label>
                <input type="text" name="tempat" class="w-full border rounded p-2">
            </div>

            {{-- Instruktur --}}
            <div class="mb-4">
                <label class="block font-semibold">Instruktur</label>
                <div id="instruktur-wrapper">
                    <div class="flex gap-2 mb-2">
                        <input type="text" name="instruktur[0][nama]" placeholder="Nama Instruktur"
                               class="w-full border rounded p-2">
                        <input type="text" name="instruktur[0][hp]" placeholder="Nomor HP"
                               class="w-full border rounded p-2">
                    </div>
                </div>
                <button type="button" onclick="addInstruktur()"
                        class="mt-2 px-3 py-1 bg-green-500 text-white rounded">
                    + Tambah Instruktur
                </button>
            </div>

            {{-- Training Officer --}}
            <div class="mb-4">
                <label class="block font-semibold">Training Officer</label>
                <div id="to-wrapper">
                    <div class="flex gap-2 mb-2">
                        <input type="text" name="training_officer[0][nama]" placeholder="Nama TO"
                               class="w-full border rounded p-2">
                        <input type="text" name="training_officer[0][hp]" placeholder="Nomor HP"
                               class="w-full border rounded p-2">
                    </div>
                </div>
                <button type="button" onclick="addTO()"
                        class="mt-2 px-3 py-1 bg-blue-500 text-white rounded">
                    + Tambah Training Officer
                </button>
            </div>

            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Simpan
            </button>

        </form>

    </div>

    {{-- SCRIPT --}}
    <script>
        let insIndex = 1;
        let toIndex = 1;

        function addInstruktur() {
            let html = `
                <div class="flex gap-2 mb-2">
                    <input type="text" name="instruktur[${insIndex}][nama]" placeholder="Nama Instruktur"
                           class="w-full border rounded p-2">
                    <input type="text" name="instruktur[${insIndex}][hp]" placeholder="Nomor HP"
                           class="w-full border rounded p-2">
                </div>
            `;
            document.getElementById('instruktur-wrapper').insertAdjacentHTML('beforeend', html);
            insIndex++;
        }

        function addTO() {
            let html = `
                <div class="flex gap-2 mb-2">
                    <input type="text" name="training_officer[${toIndex}][nama]" placeholder="Nama TO"
                           class="w-full border rounded p-2">
                    <input type="text" name="training_officer[${toIndex}][hp]" placeholder="Nomor HP"
                           class="w-full border rounded p-2">
                </div>
            `;
            document.getElementById('to-wrapper').insertAdjacentHTML('beforeend', html);
            toIndex++;
        }
    </script>
@endsection
</x-app-layout>
