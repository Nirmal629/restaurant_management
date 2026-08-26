<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user()->load('employee.role', 'employee.branch');

        return view('profile', [
            'profileUser' => $user,
            'profileEmployee' => $user->employee,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:160',
                Rule::unique(User::class, 'email')->ignore($user->id),
                Rule::unique(Employee::class, 'email')->ignore($employee?->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(Employee::class, 'phone')->ignore($employee?->id),
            ],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if ($employee) {
            $employee->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        }

        return back()->with('status', 'Profile updated successfully.');
    }
}
