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
        return Inertia::render('Admin/PickupPoints/Index', ['pickupPoints' => PickupPoint::latest()->get()]);
    }

    public function activeList()
    {
        // For the checkout page's pickup-point dropdown
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

        abort_if($pickupPoint->orders()->exists(), 422, 'Cannot delete a pickup point with existing orders. Deactivate it instead.');

        $pickupPoint->delete();

        return back()->with('success', 'Pickup point removed.');
    }
}
