<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTrainingRequest;
use App\Models\Training;
use Inertia\Inertia;

class TrainingController extends Controller
{
    public function index()
    {
        return Inertia::render('Training/Show', ['training' => Training::first()]);
    }

    public function edit()
    {
        $this->authorize('update', Training::class);

        return Inertia::render('Admin/Training/Edit', ['training' => Training::firstOrNew()]);
    }

    public function update(UpdateTrainingRequest $request)
    {
        Training::updateOrCreate(['id' => 1], $request->validated());

        return back()->with('success', 'Training page updated.');
    }
}
