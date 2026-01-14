<?php

namespace App\Http\Controllers;

use App\Models\MasterTraining;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterTrainingController extends Controller
{
    /* ================== LIST ================== */
    public function index()
{
    $masters = MasterTraining::withCount('trainings')
        ->orderByDesc('id')
        ->paginate(10);

    return view('master-training.index', compact('masters'));
}


    /* ================== CREATE ================== */
    public function create()
    {
        return view('master-training.create');
    }

    /* ================== STORE ================== */
    public function store(Request $request)
    {
        $request->validate([
            'nama_training' => 'required|string|max:255',
            'kategori'      => 'nullable|string|max:255',

            // bulk training
            'codes'         => 'required|array|min:1',
            'codes.*'       => 'required|string|max:50',

            'names'         => 'required|array|min:1',
            'names.*'       => 'required|string|max:255',

            'descriptions'  => 'nullable|array',
            'descriptions.*'=> 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {

            /* ===== CREATE MASTER ===== */
            $master = MasterTraining::create([
                'nama_training' => $request->nama_training,
                'kategori'      => $request->kategori,
            ]);

            /* ===== CREATE TRAININGS (ANAK) ===== */
            foreach ($request->names as $i => $name) {
                Training::create([
                    'master_training_id' => $master->id,
                    'code'               => $request->codes[$i],
                    'name'               => $name,
                    'description'        => $request->descriptions[$i] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('master-training.index')
            ->with('success', 'Master training & daftar training berhasil dibuat');
    }

    /* ================== SHOW ================== */
    public function show(MasterTraining $masterTraining)
    {
        $masterTraining->load('trainings');

        return view('master-training.show', [
            'master' => $masterTraining
        ]);
    }
}
