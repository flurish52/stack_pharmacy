<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureAdminArea;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

// Access is enforced by the can:manage-staff route middleware.
class StaffController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();

        $staff = User::role(EnsureAdminArea::ROLES)
            ->with('roles:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->first(),
                'is_me' => $user->is($actor),
                'can_manage' => $actor->canManageStaff($user),
            ]);

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $staff,
            'assignableRoles' => $actor->assignableRoles(),
        ]);
    }

    public function store(StoreStaffRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                // Nobody knows this password; the person sets their own through the invite link.
                'password' => Hash::make(Str::random(40)),
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->syncRoles([$data['role']]);

            return $user;
        });

        activity('staff')
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties(['role' => $data['role']])
            ->log('Staff account created');

        return match ($this->sendInvite($user)) {
            'sent' => back()->with('success', "Staff account created. An invite email was sent to {$user->email}."),
            default => back()->with('error', 'Account created, but the invite email could not be sent. Use "Resend invite" on that row.'),
        };
    }

    public function update(UpdateStaffRequest $request, User $user)
    {
        abort_unless($request->user()->canManageStaff($user), 403);

        $data = $request->validated();
        $oldRole = $user->roles->pluck('name')->first();

        $user->fill(['name' => $data['name']]);
        $nameChanged = $user->isDirty('name');
        $user->save();

        if ($oldRole !== $data['role']) {
            $user->syncRoles([$data['role']]);

            activity('staff')
                ->performedOn($user)
                ->causedBy($request->user())
                ->withProperties(['from' => $oldRole, 'to' => $data['role']])
                ->log('Staff role changed');
        } elseif ($nameChanged) {
            activity('staff')->performedOn($user)->causedBy($request->user())->log('Staff name updated');
        }

        return back()->with('success', 'Staff member updated.');
    }

    public function resendInvite(Request $request, User $user)
    {
        abort_unless($request->user()->canManageStaff($user), 403);

        return match ($this->sendInvite($user)) {
            'sent' => back()->with('success', "Invite email sent to {$user->email}."),
            'throttled' => back()->with('error', 'An invite was just sent. Please wait a minute before sending another.'),
            default => back()->with('error', 'The invite email could not be sent. Please try again.'),
        };
    }

    /**
     * Removes admin-area access. The account stays (as a customer), so
     * everything they did remains attributed to them in the activity log.
     */
    public function revoke(Request $request, User $user)
    {
        abort_unless($request->user()->canManageStaff($user), 403);

        $oldRole = $user->roles->pluck('name')->first();

        $user->syncRoles(['customer']);

        activity('staff')
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties(['from' => $oldRole, 'to' => 'customer'])
            ->log('Staff access revoked');

        return back()->with('success', "{$user->name} no longer has access to the admin area.");
    }

    /** Emails a set-your-password link. Returns 'sent', 'throttled' or 'failed'. */
    private function sendInvite(User $user): string
    {
        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $e) {
            report($e);

            return 'failed';
        }

        return match ($status) {
            Password::RESET_LINK_SENT => 'sent',
            Password::RESET_THROTTLED => 'throttled',
            default => 'failed',
        };
    }
}
