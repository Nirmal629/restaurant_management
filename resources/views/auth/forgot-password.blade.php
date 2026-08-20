<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Forgot Password | Restaurant OS</title>
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
                        <p class="mb-4 text-sm font-medium uppercase tracking-[0.25em] text-brand-300">Account Recovery</p>
                        <h2 class="text-4xl font-bold leading-tight">Secure access for your team, even when passwords are forgotten.</h2>
                        <p class="mt-5 max-w-md text-base text-slate-300">
                            Reset access quickly and keep the restaurant floor moving without compromising security.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Recovery</p>
                        <p class="mt-2 text-lg font-semibold">Email or phone</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Security</p>
                        <p class="mt-2 text-lg font-semibold">Instant validation</p>
                    </div>
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
                                <p class="text-sm font-medium uppercase tracking-[0.2em] text-brand-600">Recovery</p>
                                <h2 class="mt-2 text-3xl font-bold text-slate-900">Forgot password?</h2>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M10.5 16.5 4.5 12l6-4.5"/>
                                    <path d="M13.5 7.5 19.5 12l-6 4.5"/>
                                </svg>
                            </div>
                        </div>

                        <p class="mb-6 text-sm text-slate-500">
                            Enter the email address or mobile number connected to your account. We’ll send a secure recovery link.
                        </p>

                        <form class="space-y-5" method="POST" action="#">
                            <div>
                                <label for="recovery" class="mb-2 block text-sm font-medium text-slate-700">Email or Mobile</label>
                                <input id="recovery" type="text" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200" placeholder="Enter your email or mobile number" />
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:ring-offset-2">
                                Send reset link
                            </button>
                        </form>

                        <div class="mt-6 border-t border-slate-200 pt-4 text-center text-sm text-slate-500">
                            Remember your password? <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Back to sign in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
