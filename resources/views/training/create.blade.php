<x-app-layout>

    <div class="max-w-3xl mx-auto px-6 py-10">

        <!-- TITLE -->
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">
            Tambah Master Training
        </h1>

        <form method="POST" action="{{ route('training.store') }}"
              class="bg-white shadow-lg rounded-lg p-6 border">
            @csrf

            <div class="mb-4">
                <label class="block font-semibold mb-1">Kode Alias</label>
                <input name="code" type="text"
                    class="w-full p-3 border rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: ROF">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Nama Training</label>
                <input name="name" type="text"
                    class="w-full p-3 border rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: Rigged & Operation Forklift">
            </div>

            <div class="mb-6">
                <label class="block font-semibold mb-1">Deskripsi</label>
                <textarea name="description" rows="4"
                    class="w-full p-3 border rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <!-- SUBMIT BUTTON FIXED -->
            <button type="submit"
                class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow
                       hover:bg-green-700 hover:shadow-md transition">
                Simpan
            </button>

        </form>

    </div>

</x-app-layout>
