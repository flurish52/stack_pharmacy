<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Permission\Models\Permission;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,

                'roles' => $user ? $user->getRoleNames()->values() : [],

                'permissions' => $user
                    ? ($user->hasRole('super_admin')
                        ? Permission::pluck('name')
                        : $user->getAllPermissions()->pluck('name')->values())
                    : [],

                'isStaffMember' => $user
                    ? $user->hasAnyRole(EnsureAdminArea::ROLES)
                    : false,
            ],
            'cart' => fn () => app(\App\Support\CartSession::class)->summary(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'pharmacyWhatsapp' => config('services.pharmacy_whatsapp_number'),
            'vapidPublicKey' => config('pharmacy.vapid.public_key'),
        ];
    }
}
