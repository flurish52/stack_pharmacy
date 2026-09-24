<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAboutPageRequest;
use App\Models\AboutPage;
use App\Models\TeamMember;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class AboutPageController extends Controller
{
    /** Public "/about-us" page. */
    public function show(): Response
    {
        return Inertia::render('About/AboutUs', [
            'about' => AboutPage::current(),
            'team' => TeamMember::all(),
        ]);
    }

    /** Admin edit screen — story/mission/vision + team list. */
    public function adminIndex(): Response
    {
        return Inertia::render('Admin/About/Index', [
            'about' => AboutPage::current(),
            'team' => TeamMember::all(),
        ]);
    }

    /**
     * Singleton row — creates it on first save, updates it on every save
     * after that. AboutPage::current() guarantees the same row (id 1) either way.
     */
    public function update(UpdateAboutPageRequest $request)
    {
        $about = AboutPage::current();
        $about->update(Arr::only($request->validated(), ['story', 'mission', 'vision']));

        return $about->syncImage($request)
            ? back()->with('success', 'About page updated.')
            : back()->with('error', 'About page updated, but the image upload failed. Try again.');
    }
}
