<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index()
    {

        return Inertia::render('Services/Index', [
            'services' => Service::where('is_active', true)->get(),
        ]);
    }

    public function adminIndex()
    {
        return Inertia::render('Admin/Services/Index', ['services' => Service::latest()->get()]);
    }

    public function store(StoreServiceRequest $request)
    {
        Service::create($request->validated());

        return back()->with('success', 'Service created.');
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());

        return back()->with('success', 'Service updated.');
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        $service->delete();

        return back()->with('success', 'Service removed.');
    }
}
