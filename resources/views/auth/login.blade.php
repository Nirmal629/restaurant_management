<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login | Restaurant OS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div class="min-h-screen grid lg:grid-cols-[42%_58%]">
            <!-- Left brand panel -->
            <div class="relative hidden overflow-hidden bg-slate-900 px-8 py-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(244,63,94,0.18),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(245,158,11,0.15),_transparent_30%)]"></div>

                <div class="relative z-10">
                    <div class="mb-10 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600/20 ring-1 ring-white/10">
                            <svg viewBox="0 0 24 24" class="h-6 w-6 text-brand-400" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10.5V8.8A2.8 2.8 0 0 1 6.8 6h10.4A2.8 2.8 0 0 1 20 8.8v1.7"/>
                                <path d="M5.5 10.5h13l-1.2 7.2a2.2 2.2 0 0 1-2.2 1.8H8.9a2.2 2.2 0 0 1-2.2-1.8L5.5 10.5Z"/>
                                <path d="M8 10.5V8.4A4 4 0 0 1 12 4.5a4 4 0 0 1 4 3.9v2.1"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-300">Restaurant OS</p>
                            <h1 class="text-2xl font-bold">DineFlow</h1>
                        </div>
                    </div>

                    <div class="max-w-md">
                        <p class="mb-4 text-sm font-medium uppercase tracking-[0.25em] text-brand-300">Restaurant Control Center</p>
                        <h2 class="text-4xl font-bold leading-tight">Smarter dine-in operations from table to settlement.</h2>
                        <p class="mt-5 max-w-md text-base text-slate-300">
                            Manage reservations, POS orders, kitchen flow, billing, inventory, and reports from a single place.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 mt-10 space-y-4">
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500/15 text-brand-300">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 18.5h16"/>
                                <path d="M7 15.5V9.5a5 5 0 0 1 10 0v6"/>
                                <path d="M9.5 7.5h5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Kitchen visibility</p>
                            <p class="text-sm text-slate-300">Live order tracking and station readiness</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/15 text-amber-300">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14"/>
                                <path d="M12 5v14"/>
                                <circle cx="12" cy="12" r="8.5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Real-time billing</p>
                            <p class="text-sm text-slate-300">Fast checks, payments, and receipt closing</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-300">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5v-9Z"/>
                                <path d="M4 9h16"/>
                                <path d="M8 14.5h8"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Inventory confidence</p>
                            <p class="text-sm text-slate-300">Low stock, purchases, wastage, and stock counts</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right login panel -->
            <div class="flex items-center justify-center bg-slate-50 px-4 py-8 sm:px-8 lg:px-12">
                <div class="w-full max-w-md">
                    <div class="mb-8 lg:hidden">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 ring-1 ring-brand-100">
                                <svg viewBox="0 0 24 24" class="h-6 w-6 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 10.5V8.8A2.8 2.8 0 0 1 6.8 6h10.4A2.8 2.8 0 0 1 20 8.8v1.7"/>
                                    <path d="M5.5 10.5h13l-1.2 7.2a2.2 2.2 0 0 1-2.2 1.8H8.9a2.2 2.2 0 0 1-2.2-1.8L5.5 10.5Z"/>
                                    <path d="M8 10.5V8.4A4 4 0 0 1 12 4.5a4 4 0 0 1 4 3.9v2.1"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">Restaurant OS</p>
                                <h1 class="text-2xl font-bold text-slate-900">DineFlow</h1>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div class="mb-6">
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-brand-600">Welcome back</p>
                            <h2 class="mt-2 text-3xl font-bold text-slate-900">Sign in to your workspace</h2>
                            <p class="mt-2 text-sm text-slate-500">Access your restaurant dashboard, POS, and kitchen controls.</p>
                        </div>

                        <form class="space-y-5" method="GET" action="{{ route('dashboard') }}">
                            <div>
                                <label for="branch" class="mb-2 block text-sm font-medium text-slate-700">Restaurant / Branch</label>
                                <select id="branch" name="branch" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                                    <option>Sunset Bistro - Main Branch</option>
                                    <option>Sunset Bistro - Downtown</option>
                                    <option>Garden Table - Rooftop</option>
                                </select>
                            </div>

                            <div>
                                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email or Mobile</label>
                                <input id="email" type="text" value="manager@sunsetbistro.com" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="Enter your email or phone number" />
                            </div>

                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Forgot password?</a>
                                </div>
                                <div class="relative">
                                    <input id="password" type="password" value="password123" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 pr-11 text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="Enter your password" />
                                    <button type="button" class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-slate-700" aria-label="Toggle password visibility">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                                    Remember me
                                </label>
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-medium uppercase tracking-wide text-emerald-700">Secure login</span>
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:ring-offset-2">
                                Sign in
                            </button>
                        </form>

                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-amber-800">Quick Staff PIN</p>
                                    <p class="text-xs text-amber-700">For waiters, cashiers, and kitchen staff</p>
                                </div>
                                <button type="button" class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">
                                    Use PIN
                                </button>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <input type="password" value="1234" class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-200" placeholder="Enter your PIN" />
                            </div>
                        </div>

                        <div class="mt-6 border-t border-slate-200 pt-4 text-center text-sm text-slate-500">
                            Need help? <a href="#" class="font-medium text-brand-600 hover:text-brand-700">Contact support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
