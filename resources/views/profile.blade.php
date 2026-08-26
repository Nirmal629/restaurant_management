<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile | Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
@php
    $employee = $profileEmployee;
    $initials = collect(explode(' ', $employee?->name ?? $profileUser->name ?? 'User'))
        ->filter()
        ->map(fn ($word) => strtoupper($word[0]))
        ->take(2)
        ->implode('') ?: 'U';
@endphp
<body class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">
    <x-shell.sidebar active="profile" />

    <div class="adm-main">
        <x-admin.page-header title="My Profile" subtitle="View and update your own account details">
            <a href="{{ route('password.change') }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-[12px] font-black text-slate-700 shadow-sm hover:bg-slate-50">
                Change Password
            </a>
        </x-admin.page-header>

        <main class="min-h-0 flex-1 overflow-auto p-3">
            <div class="grid gap-3 xl:grid-cols-[0.8fr_1.2fr]">
                <section class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Account Snapshot</p>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-3">
                            <span class="grid h-14 w-14 place-items-center rounded-xl bg-brand-600 text-[16px] font-black text-white">{{ $initials }}</span>
                            <div class="min-w-0">
                                <h2 class="truncate text-[20px] font-black text-slate-950">{{ $employee?->name ?? $profileUser->name }}</h2>
                                <p class="truncate text-[12px] font-semibold text-slate-500">{{ $employee?->role?->name ?? 'Staff Member' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[9.5px] font-black uppercase text-slate-400">Employee ID</p>
                                <p class="pos-num mt-1 text-[13px] font-bold text-slate-900">{{ $employee?->employee_code ?? 'Not assigned' }}</p>
                            </div>
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[9.5px] font-black uppercase text-slate-400">Branch</p>
                                <p class="mt-1 text-[13px] font-bold text-slate-900">{{ $employee?->branch?->name ?? 'Not assigned' }}</p>
                            </div>
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[9.5px] font-black uppercase text-slate-400">Shift</p>
                                <p class="mt-1 text-[13px] font-bold text-slate-900">{{ ucfirst($employee?->shift ?? 'Not assigned') }}</p>
                            </div>
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[9.5px] font-black uppercase text-slate-400">Status</p>
                                <span class="mt-1 inline-flex rounded border border-emerald-300 bg-emerald-50 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-700">
                                    {{ $employee?->status ?? 'active' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-500">Profile Details</p>
                    </div>

                    <div class="p-4">
                        @if (session('status'))
                            <div class="mb-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] font-bold text-emerald-700">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-3 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-[12px] font-bold text-rose-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.update') }}" class="grid gap-3 md:grid-cols-2">
                            @csrf
                            @method('PUT')

                            <label class="block">
                                <span class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Full Name</span>
                                <input name="name" value="{{ old('name', $employee?->name ?? $profileUser->name) }}" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[13px] font-bold text-slate-900 outline-none focus:border-slate-900">
                            </label>

                            <label class="block">
                                <span class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Login Email</span>
                                <input name="email" type="email" value="{{ old('email', $employee?->email ?? $profileUser->email) }}" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[13px] font-bold text-slate-900 outline-none focus:border-slate-900">
                            </label>

                            <label class="block">
                                <span class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Phone</span>
                                <input name="phone" value="{{ old('phone', $employee?->phone) }}" class="pos-num w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[13px] font-bold text-slate-900 outline-none focus:border-slate-900">
                            </label>

                            <label class="block">
                                <span class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Role</span>
                                <input value="{{ $employee?->role?->name ?? 'Staff Member' }}" disabled class="w-full rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-[13px] font-bold text-slate-500">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="mb-1 block text-[10px] font-black uppercase tracking-wide text-slate-500">Address</span>
                                <textarea name="address" rows="4" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-[13px] font-bold text-slate-900 outline-none focus:border-slate-900">{{ old('address', $employee?->address) }}</textarea>
                            </label>

                            <div class="flex items-center justify-end gap-2 md:col-span-2">
                                <a href="{{ route('dashboard') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-[12px] font-black text-slate-700 hover:bg-slate-50">Cancel</a>
                                <button type="submit" class="rounded-md bg-slate-950 px-4 py-2 text-[12px] font-black text-white hover:bg-slate-800">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
