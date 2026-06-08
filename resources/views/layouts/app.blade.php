<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'NoahFace Sync') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    </head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <nav class="bg-white border-b border-gray-200 shadow-sm relative z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            
            {{-- Logo --}}
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ url('/') }}" class="text-xl font-bold text-blue-600 whitespace-nowrap">
                    NoahFace Sync
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex md:items-center md:space-x-6 lg:space-x-8">
                <a href="{{ route('awards.index') }}" class="text-gray-600 hover:text-blue-600 transition-colors">Awards</a>
                <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-blue-600 transition-colors">Employees</a>
                <a href="{{ route('attendance.timesheet') }}" class="text-gray-600 hover:text-blue-600 transition-colors">Timesheets</a>
            </div>

            {{-- Desktop Right Side: User Dropdown --}}
            <div class="hidden md:flex md:items-center relative">
                @auth
                    <button type="button" id="user-menu-button" class="flex items-center text-sm text-gray-600 hover:text-blue-600 focus:outline-none transition-colors">
                        <span class="mr-1">Hi, {{ auth()->user()->name }}</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div id="user-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-200">
                        
                        {{-- SMART 2FA CHECK --}}
                        @if(auth()->user()->google2fa_secret)
                            <div class="block px-4 py-2 text-sm text-green-600 font-semibold border-b border-gray-100 bg-green-50">
                                ✓ 2FA Enabled
                            </div>
                        @else
                            <a href="{{ route('2fa.setup') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                Enable 2FA
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-red-600 focus:outline-none">
                                Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </div>

            {{-- Mobile Hamburger Button --}}
            <div class="flex items-center md:hidden">
                <button type="button" id="mobile-menu-button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- Mobile Dropdown Menu --}}
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 absolute w-full shadow-lg">
        <div class="px-4 pt-2 pb-3 space-y-1">
            <a href="{{ route('awards.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50">Awards</a>
            <a href="{{ route('employees.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50">Employees</a>
            <a href="{{ route('attendance.timesheet') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50">Timesheets</a>
        </div>
        
        @auth
            <div class="pt-4 pb-4 border-t border-gray-200">
                <div class="px-4 space-y-1">
                    <div class="px-3 text-base font-medium text-gray-800 mb-2">Hi, {{ auth()->user()->name }}</div>
                    
                    {{-- SMART 2FA CHECK FOR MOBILE --}}
                    @if(auth()->user()->google2fa_secret)
                        <div class="block px-3 py-2 rounded-md text-base font-medium text-green-600 bg-green-50">✓ 2FA Enabled</div>
                    @else
                        <a href="{{ route('2fa.setup') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50">Enable 2FA</a>
                    @endif
                    
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 focus:outline-none">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
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

        {{-- 2. The dynamic content from your views (create.blade.php, etc.) will appear here --}}
        @yield('content')
    </main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const desktopBtn = document.getElementById('user-menu-button');
        const desktopMenu = document.getElementById('user-dropdown');
        
        const mobileBtn = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        // Toggle Desktop Dropdown
        if (desktopBtn && desktopMenu) {
            desktopBtn.addEventListener('click', function(event) {
                event.stopPropagation();
                desktopMenu.classList.toggle('hidden');
            });

            // Close when clicking outside
            document.addEventListener('click', function(event) {
                if (!desktopMenu.contains(event.target) && !desktopBtn.contains(event.target)) {
                    desktopMenu.classList.add('hidden');
                }
            });
        }

        // Toggle Mobile Menu
        if (mobileBtn && mobileMenu) {
            mobileBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script>
</body>
</html>