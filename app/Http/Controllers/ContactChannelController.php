<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactChannelRequest;
use App\Http\Requests\UpdateContactChannelRequest;
use App\Models\ContactChannel;
use Inertia\Inertia;

class ContactChannelController extends Controller
{
    public function index()
    {
        return Inertia::render('Contact/Index', [
            'channels' => ContactChannel::orderBy('display_order')->get(),
        ]);
    }

    public function adminIndex()
    {
        return Inertia::render('Admin/ContactChannels/Index', [
            'channels' => ContactChannel::orderBy('display_order')->get(),
        ]);
    }

    public function store(StoreContactChannelRequest $request)
    {
        ContactChannel::create($request->validated());

        return back()->with('success', 'Channel added.');
    }

    public function update(UpdateContactChannelRequest $request, ContactChannel $contactChannel)
    {
        $contactChannel->update($request->validated());

        return back()->with('success', 'Channel updated.');
    }

    public function destroy(ContactChannel $contactChannel)
    {
        $this->authorize('delete', $contactChannel);

        $contactChannel->delete();

        return back()->with('success', 'Channel removed.');
    }
}
