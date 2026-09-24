<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureAdminArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Backs the `dashboard` route (Breeze redirects here after login).
 * Staff-type roles go to the admin dashboard, customers to their account.
 */
class HomeRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->hasAnyRole(EnsureAdminArea::ROLES)
            ? redirect()->route('admin.dashboard')
            : redirect()->route('account.orders.index');
    }
}
