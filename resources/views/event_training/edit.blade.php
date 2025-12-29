<x-app-layout>

    <div class="p-8 max-w-4xl mx-auto">

        <h1 class="text-2xl font-bold mb-6">Edit Event Training</h1>

        <form action="{{ route('event-training.update', $eventTraining->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block font-semibold mb-1">Training</label>
                <input type="text" value="{{ $eventTraining->training->name }}" disabled
                       class="w-full border p-2 rounded bg-gray-100">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Job Number</label>
                <input name="job_number" type="text"
                       value="{{ $eventTraining->job_number }}"
                       class="w-full border p-2 rounded">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Tanggal</label>
                <input name="tanggal" type="date"
                       value="{{ $eventTraining->tanggal }}"
                       class="w-full border p-2 rounded">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Tempat</label>
                <input name="tempat" type="text"
                       value="{{ $eventTraining->tempat }}"
                       class="w-full border p-2 rounded">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Sertifikasi</label>
                <input name="sertifikasi" type="text"
                       value="{{ $eventTraining->sertifikasi }}"
                       class="w-full border p-2 rounded">
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Update
            </button>
        </form>

    </div>

</x-app-layout>
