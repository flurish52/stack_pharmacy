<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRequest;
use App\Models\TeamMember;
use Illuminate\Support\Arr;

class TeamMemberController extends Controller
{
    public function store(StoreTeamMemberRequest $request)
    {
        $teamMember = TeamMember::create(Arr::only($request->validated(), ['name', 'role', 'bio', 'sort_order']));

        return $teamMember->syncImage($request)
            ? back()->with('success', 'Team member added.')
            : back()->with('error', 'Team member added, but the image upload failed. Try again.');
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember)
    {
        $teamMember->update(Arr::only($request->validated(), ['name', 'role', 'bio', 'sort_order']));

        return $teamMember->syncImage($request)
            ? back()->with('success', 'Team member updated.')
            : back()->with('error', 'Team member updated, but the image upload failed. Try again.');
    }

    public function destroy(TeamMember $teamMember)
    {
        $teamMember->delete();

        return back()->with('success', 'Team member removed.');
    }
}
