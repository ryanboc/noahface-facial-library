<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'NoahFace Sync') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    </head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">
    @php
        $navItem = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors';
        $navActive = 'bg-blue-50 text-blue-700';
        $navIdle = 'text-slate-600 hover:bg-slate-50 hover:text-slate-950';
    @endphp

    <nav class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 shadow-sm backdrop-blur" aria-label="Main navigation">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-18 items-center justify-between gap-4 py-3">
                <a href="{{ url('/') }}" class="group flex shrink-0 items-center gap-3" aria-label="NoahFace Sync home">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-sm shadow-blue-200 transition-transform group-hover:-translate-y-0.5">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M7 17V7l10 10V7" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-bold tracking-tight text-slate-950">NoahFace</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Sync</span>
                    </span>
                </a>

                @auth
                    <div class="hidden items-center gap-1 lg:flex">
                        <a href="{{ route('profile.show') }}" class="rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">Dashboard</a>

                        @if(auth()->user()->canApproveLeave())
                            <div class="relative" data-nav-menu>
                                <button type="button" data-nav-trigger aria-expanded="false" class="flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ request()->routeIs('employees.*', 'companies.*', 'awards.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                                    People <svg class="h-4 w-4 transition-transform" data-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div data-nav-panel class="absolute left-0 top-full hidden w-56 pt-3">
                                    <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                                        <a href="{{ route('employees.index') }}" class="{{ $navItem }} {{ request()->routeIs('employees.*') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-violet-50 text-violet-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" d="M16 18a4 4 0 0 0-8 0M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg></span> Employees</a>
                                        <a href="{{ route('companies.index') }}" class="{{ $navItem }} {{ request()->routeIs('companies.*') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-sky-50 text-sky-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linejoin="round" d="M4 20V6l8-3 8 3v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M9 17h6"/></svg></span> Companies</a>
                                        <a href="{{ route('awards.index') }}" class="{{ $navItem }} {{ request()->routeIs('awards.*') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-50 text-amber-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linejoin="round" d="m12 3 2.5 5.2 5.5.8-4 4 .9 5.7-4.9-2.6-4.9 2.6L8 13 4 9l5.5-.8L12 3Z"/></svg></span> Awards</a>
                                    </div>
                                </div>
                            </div>

                            <div class="relative" data-nav-menu>
                                <button type="button" data-nav-trigger aria-expanded="false" class="flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ request()->routeIs('attendance.*', 'roster.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                                    Workforce <svg class="h-4 w-4 transition-transform" data-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div data-nav-panel class="absolute left-0 top-full hidden w-56 pt-3">
                                    <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                                        <a href="{{ route('attendance.status') }}" class="{{ $navItem }} {{ request()->routeIs('attendance.status') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-teal-50 text-teal-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8 2a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM2 21a6 6 0 0 1 12 0m0 0a5 5 0 0 1 8 0"/></svg></span> Who's working</a>
                                        <a href="{{ route('attendance.timesheet') }}" class="{{ $navItem }} {{ request()->routeIs('attendance.timesheet') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-50 text-emerald-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" d="M12 8v4l3 2"/></svg></span> Timesheets</a>
                                        <a href="{{ route('payslips.index') }}" class="{{ $navItem }} {{ request()->routeIs('payslips.*') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-50 text-indigo-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Z"/><path stroke-width="2" d="M8 8h8M8 12h8"/></svg></span> Payslips</a>
                                        <a href="{{ route('roster.index') }}" class="{{ $navItem }} {{ request()->routeIs('roster.*') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-blue-50 text-blue-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="2"/><path stroke-width="2" d="M8 3v4M16 3v4M4 10h16"/></svg></span> Roster</a>
                                    </div>
                                </div>
                            </div>

                            <div class="relative" data-nav-menu>
                                <button type="button" data-nav-trigger aria-expanded="false" class="flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ request()->routeIs('leave.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                                    Leave <svg class="h-4 w-4 transition-transform" data-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div data-nav-panel class="absolute left-0 top-full hidden w-56 pt-3">
                                    <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                                        <a href="{{ route('leave.index') }}" class="{{ $navItem }} {{ request()->routeIs('leave.index') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-orange-50 text-orange-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg></span> Approvals</a>
                                        <a href="{{ route('leave.calendar') }}" class="{{ $navItem }} {{ request()->routeIs('leave.calendar') ? $navActive : $navIdle }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-pink-50 text-pink-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="2"/><path stroke-width="2" d="M8 3v4M16 3v4M4 10h16"/></svg></span> Leave calendar</a>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('messages.index') }}" class="rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ request()->routeIs('messages.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">Messages</a>
                        @endif
                    </div>

                    <div class="hidden items-center lg:flex">
                        <div class="mx-3 h-8 w-px bg-slate-200"></div>
                        <div class="relative" data-nav-menu>
                            <button type="button" data-nav-trigger aria-expanded="false" class="flex items-center gap-2.5 rounded-xl p-1.5 pr-2.5 text-left transition-colors hover:bg-slate-50">
                                <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-900 text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                <span class="max-w-32 truncate text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                                <svg class="h-4 w-4 text-slate-400 transition-transform" data-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div data-nav-panel class="absolute right-0 top-full hidden w-60 pt-3">
                                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                                    <div class="border-b border-slate-100 px-4 py-3">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Account settings</p>
                                    </div>
                                    <div class="p-2">
                                        <a href="{{ route('profile.show') }}" class="{{ $navItem }} {{ $navIdle }}">My profile</a>
                                        @if(auth()->user()->google2fa_secret)
                                            <div class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> 2FA protected</div>
                                        @else
                                            <a href="{{ route('2fa.setup') }}" class="{{ $navItem }} {{ $navIdle }}">Enable 2FA</a>
                                        @endif
                                        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1">@csrf
                                            <button type="submit" class="w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-red-600 transition-colors hover:bg-red-50">Log out</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="mobile-menu-button" aria-controls="mobile-menu" aria-expanded="false" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-600 transition-colors hover:bg-slate-50 lg:hidden">
                        <span class="sr-only">Open navigation</span>
                        <svg id="menu-open-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/></svg>
                        <svg id="menu-close-icon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                @endauth
            </div>
        </div>

        @auth
            <div id="mobile-menu" class="hidden max-h-[calc(100vh-4.5rem)] overflow-y-auto border-t border-slate-200 bg-white lg:hidden">
                <div class="mx-auto max-w-7xl space-y-5 px-4 py-5 sm:px-6">
                    <div>
                        <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Overview</p>
                        <a href="{{ route('profile.show') }}" class="{{ $navItem }} {{ request()->routeIs('profile.*') ? $navActive : $navIdle }}">Dashboard</a>
                    </div>
                    @if(auth()->user()->canApproveLeave())
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div><p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">People</p>
                                <a href="{{ route('employees.index') }}" class="{{ $navItem }} {{ request()->routeIs('employees.*') ? $navActive : $navIdle }}">Employees</a>
                                <a href="{{ route('companies.index') }}" class="{{ $navItem }} {{ request()->routeIs('companies.*') ? $navActive : $navIdle }}">Companies</a>
                                <a href="{{ route('awards.index') }}" class="{{ $navItem }} {{ request()->routeIs('awards.*') ? $navActive : $navIdle }}">Awards</a>
                            </div>
                            <div><p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Work</p>
                                <a href="{{ route('attendance.status') }}" class="{{ $navItem }} {{ request()->routeIs('attendance.status') ? $navActive : $navIdle }}">Who's working</a>
                                <a href="{{ route('attendance.timesheet') }}" class="{{ $navItem }} {{ request()->routeIs('attendance.timesheet') ? $navActive : $navIdle }}">Timesheets</a>
                                <a href="{{ route('payslips.index') }}" class="{{ $navItem }} {{ request()->routeIs('payslips.*') ? $navActive : $navIdle }}">Payslips</a>
                                <a href="{{ route('roster.index') }}" class="{{ $navItem }} {{ request()->routeIs('roster.*') ? $navActive : $navIdle }}">Roster</a>
                                <a href="{{ route('messages.index') }}" class="{{ $navItem }} {{ request()->routeIs('messages.*') ? $navActive : $navIdle }}">Messages</a>
                            </div>
                            <div><p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Leave</p>
                                <a href="{{ route('leave.index') }}" class="{{ $navItem }} {{ request()->routeIs('leave.index') ? $navActive : $navIdle }}">Approvals</a>
                                <a href="{{ route('leave.calendar') }}" class="{{ $navItem }} {{ request()->routeIs('leave.calendar') ? $navActive : $navIdle }}">Leave calendar</a>
                            </div>
                        </div>
                    @endif
                    <div class="border-t border-slate-200 pt-4">
                        <div class="mb-3 flex items-center gap-3 px-3"><span class="grid h-9 w-9 place-items-center rounded-full bg-slate-900 text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span></div>
                        @unless(auth()->user()->google2fa_secret)<a href="{{ route('2fa.setup') }}" class="{{ $navItem }} {{ $navIdle }}">Enable 2FA</a>@endunless
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-red-600 hover:bg-red-50">Log out</button></form>
                    </div>
                </div>
            </div>
        @endauth
    </nav>

    <main>
        {{-- 1. Flash Messages for Success/Error alerts --}}
        @if(session('success'))
            <div class="container mx-auto mt-6 px-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if($errors->any())
            <div class="container mx-auto mt-6 px-4">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" role="alert">
                    <strong class="font-semibold">Please check the details:</strong>
                    <ul class="mt-1 list-disc list-inside text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            </div>
        @endif
        @if(session('warning'))
            <div class="container mx-auto mt-6 px-4"><div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-800" role="alert">{{ session('warning') }}</div></div>
        @endif

        {{-- 2. The dynamic content from your views (create.blade.php, etc.) will appear here --}}
        @yield('content')
    </main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileBtn = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const openIcon = document.getElementById('menu-open-icon');
        const closeIcon = document.getElementById('menu-close-icon');
        const navMenus = Array.from(document.querySelectorAll('[data-nav-menu]'));

        function closeDesktopMenus(except) {
            navMenus.forEach(function(menu) {
                if (menu === except) return;
                menu.querySelector('[data-nav-panel]').classList.add('hidden');
                menu.querySelector('[data-nav-trigger]').setAttribute('aria-expanded', 'false');
                menu.querySelector('[data-chevron]')?.classList.remove('rotate-180');
            });
        }

        navMenus.forEach(function(menu) {
            const trigger = menu.querySelector('[data-nav-trigger]');
            const panel = menu.querySelector('[data-nav-panel]');

            trigger.addEventListener('click', function(event) {
                event.stopPropagation();
                const willOpen = panel.classList.contains('hidden');
                closeDesktopMenus(menu);
                panel.classList.toggle('hidden', !willOpen);
                trigger.setAttribute('aria-expanded', String(willOpen));
                menu.querySelector('[data-chevron]')?.classList.toggle('rotate-180', willOpen);
            });
        });

        if (mobileBtn && mobileMenu) {
            mobileBtn.addEventListener('click', function() {
                const willOpen = mobileMenu.classList.contains('hidden');
                mobileMenu.classList.toggle('hidden', !willOpen);
                mobileBtn.setAttribute('aria-expanded', String(willOpen));
                openIcon.classList.toggle('hidden', willOpen);
                closeIcon.classList.toggle('hidden', !willOpen);
            });
        }

        document.addEventListener('click', function() { closeDesktopMenus(); });
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDesktopMenus();
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) mobileBtn.click();
            }
        });
    });
</script>
</body>
</html>
