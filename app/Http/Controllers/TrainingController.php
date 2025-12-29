<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function index()
    {
        $trainings = Training::orderBy('id', 'DESC')->get();
        return view('training.index', compact('trainings'));
    }

    public function create()
    {
        return view('training.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:trainings',
            'name' => 'required',
        ]);

        Training::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('training.index')->with('success', 'Training berhasil ditambahkan!');
    }
}
