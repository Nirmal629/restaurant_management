<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Employees log in with the email on their staff record — the same
     * users seeded in database/seeders/EmployeeSeeder.php.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = $request->user();
        $employee = $user?->employee;
        if (! $employee || $employee->status !== 'active') {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'This staff account is not active.',
            ]);
        }

        $request->session()->regenerate();

        return redirect($this->firstAllowedRouteFor($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function start(Request $request): RedirectResponse
    {
        return redirect($this->firstAllowedRouteFor($request->user()));
    }

    public static function allowedRoutePermissions(): array
    {
        return [
            'dashboard' => ['Dashboard', 'View'],
            'pos' => ['POS', 'View'],
            'tables' => ['Orders', 'View'],
            'orders' => ['Orders', 'View'],
            'kds' => ['Kitchen', 'View'],
            'reservations' => ['Orders', 'View'],
            'customers' => ['Customers', 'View'],
            'menu' => ['Menu', 'View'],
            'inventory' => ['Inventory', 'View'],
            'purchases' => ['Purchases', 'View'],
            'expenses' => ['Expenses', 'View'],
            'billing' => ['Billing', 'View'],
            'reports' => ['Reports', 'View'],
            'employees' => ['Employees', 'View'],
            'settings' => ['Settings', 'View'],
        ];
    }

    private function firstAllowedRouteFor($user): string
    {
        foreach (self::allowedRoutePermissions() as $route => $permission) {
            if (Route::has($route) && $user->hasPermission($permission[0], $permission[1])) {
                return route($route);
            }
        }

        return route('profile.edit');
    }
}
