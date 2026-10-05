<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePickupPointRequest;
use App\Http\Requests\UpdatePickupPointRequest;
use App\Models\PickupPoint;
use Inertia\Inertia;

class PickupPointController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/PickupPoints/Index', [
            'pickupPoints' => PickupPoint::withCount('orders')->latest()->get(),
        ]);
    }

    /** For the checkout page's pickup-point dropdown. */
    public function activeList()
    {
        return PickupPoint::where('is_active', true)->get(['id', 'name', 'address']);
    }

    public function store(StorePickupPointRequest $request)
    {
        PickupPoint::create($request->validated());

        return back()->with('success', 'Pickup point added.');
    }

    public function update(UpdatePickupPointRequest $request, PickupPoint $pickupPoint)
    {
        $pickupPoint->update($request->validated());

        return back()->with('success', 'Pickup point updated.');
    }

    public function destroy(PickupPoint $pickupPoint)
    {
        $this->authorize('delete', $pickupPoint);

        if ($pickupPoint->orders()->exists()) {
            return back()->with('error', 'This pickup point has existing orders, so it cannot be deleted. Deactivate it instead.');
        }

        $pickupPoint->delete();

        return back()->with('success', 'Pickup point removed.');
    }
}
