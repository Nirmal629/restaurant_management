<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password | Restaurant OS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div class="min-h-screen grid lg:grid-cols-[42%_58%]">
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
                        <p class="mb-4 text-sm font-medium uppercase tracking-[0.25em] text-brand-300">Password Security</p>
                        <h2 class="text-4xl font-bold leading-tight">Create a new secure password for your restaurant account.</h2>
                        <p class="mt-5 max-w-md text-base text-slate-300">
                            This protects staff access to the POS, kitchen, inventory, and financial workflows.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Policy</p>
                    <p class="mt-2 text-lg font-semibold">Minimum 8 characters with mixed case and number</p>
                </div>
            </div>

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
                        <div class="mb-6 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium uppercase tracking-[0.2em] text-brand-600">Security</p>
                                <h2 class="mt-2 text-3xl font-bold text-slate-900">Reset password</h2>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7 11V8a5 5 0 0 1 10 0v3"/>
                                    <rect x="4.5" y="11" width="15" height="9" rx="2"/>
                                </svg>
                            </div>
                        </div>

                        <form class="space-y-5" method="POST" action="#">
                            <div>
                                <label for="new-password" class="mb-2 block text-sm font-medium text-slate-700">New password</label>
                                <div class="relative">
                                    <input id="new-password" type="password" value="NewPass123" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 pr-11 text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="Enter a new password" />
                                    <button type="button" class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-slate-700" aria-label="Toggle password visibility">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="confirm-password" class="mb-2 block text-sm font-medium text-slate-700">Confirm password</label>
                                <div class="relative">
                                    <input id="confirm-password" type="password" value="NewPass123" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 pr-11 text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="Confirm your new password" />
                                    <button type="button" class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-slate-700" aria-label="Toggle password visibility">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                                <p class="text-sm text-emerald-700">Password strength: Strong</p>
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:ring-offset-2">
                                Update password
                            </button>
                        </form>

                        <div class="mt-6 border-t border-slate-200 pt-4 text-center text-sm text-slate-500">
                            Need another route? <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Back to sign in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
