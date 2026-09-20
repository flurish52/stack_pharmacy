<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingRequest;
use App\Http\Requests\UpdateTrainingRequest;
use App\Models\Training;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class TrainingController extends Controller
{
    /** Public training page. */
    public function index()
    {
        return Inertia::render('Training/Index', [
            'training' => Training::latest()->get(),
        ]);
    }

    // Admin actions are protected by the can:manage-training route middleware.
    public function adminIndex()
    {
        return Inertia::render('Admin/Training/Index', [
            'trainings' => Training::latest()->get(),
        ]);
    }

    public function store(StoreTrainingRequest $request)
    {
        $training = Training::create(Arr::only($request->validated(), ['title', 'description']));

        return $training->syncImage($request)
            ? back()->with('success', 'Training added.')
            : back()->with('error', 'Training added, but the image upload failed. Try adding the image again.');
    }

    public function update(UpdateTrainingRequest $request, Training $training)
    {
        $training->update(Arr::only($request->validated(), ['title', 'description']));

        return $training->syncImage($request)
            ? back()->with('success', 'Training updated.')
            : back()->with('error', 'Training updated, but the image upload failed. Try again.');
    }

    public function destroy(Training $training)
    {
        $training->delete(); // HasCloudinaryImage also removes the Cloudinary file

        return back()->with('success', 'Training removed.');
    }
}
