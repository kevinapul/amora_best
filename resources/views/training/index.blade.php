<x-app-layout>

    <div class="max-w-5xl mx-auto px-6 py-10">

        <!-- TITLE -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">
                Master Training
            </h1>

            <!-- BUTTON FIXED 100% -->
            <a href="{{ route('training.create') }}"
               class="inline-block px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow
                      hover:bg-blue-700 hover:shadow-md transition">
                + Tambah Training
            </a>
        </div>

        <!-- TABLE -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden border">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr class="text-gray-700 border-b">
                        <th class="py-3 px-4 font-semibold">Code</th>
                        <th class="py-3 px-4 font-semibold">Nama Training</th>
                        <th class="py-3 px-4 font-semibold">Deskripsi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($trainings as $t)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="py-3 px-4">{{ $t->code }}</td>
                            <td class="py-3 px-4 font-semibold">{{ $t->name }}</td>
                            <td class="py-3 px-4">{{ $t->description }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    </div>

</x-app-layout>
