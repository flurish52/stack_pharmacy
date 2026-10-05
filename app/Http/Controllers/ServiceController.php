<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ServiceController extends Controller
{
    /** Public services page. */
    public function index()
    {
        return Inertia::render('Services/Index', [
            'services' => Service::where('is_active', true)->get(),
        ]);
    }

    public function adminIndex()
    {
        return Inertia::render('Admin/Services/Index', [
            'services' => Service::latest()->get(),
        ]);
    }

    public function store(StoreServiceRequest $request)
    {
        $service = Service::create(Arr::except($request->validated(), ['image', 'remove_image']));

        return $service->syncImage($request)
            ? back()->with('success', 'Service created.')
            : back()->with('error', 'Service created, but the image upload failed. Try adding the image again.');
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update(Arr::except($request->validated(), ['image', 'remove_image']));

        return $service->syncImage($request)
            ? back()->with('success', 'Service updated.')
            : back()->with('error', 'Service updated, but the image upload failed. Try again.');
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        $service->delete(); // HasCloudinaryImage also removes the Cloudinary file

        return back()->with('success', 'Service removed.');
    }
}
