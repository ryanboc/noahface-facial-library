<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'NoahFace Sync') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    </head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <nav class="bg-white border-b border-gray-200 shadow-sm relative">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                
                {{-- Logo (Always visible) --}}
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="text-xl font-bold text-blue-600 whitespace-nowrap">
                        NoahFace Sync
                    </a>
                </div>

                {{-- Desktop Navigation (Hidden on mobile) --}}
                <div class="hidden md:flex md:items-center md:space-x-6 lg:space-x-8">
                    <a href="{{ route('awards.index') }}" class="text-gray-600 hover:text-blue-600 transition-colors">Awards</a>
                    <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-blue-600 transition-colors">Employees</a>
                    <a href="{{ route('attendance.timesheet') }}" class="text-gray-600 hover:text-blue-600 transition-colors">Timesheets</a>
                </div>

                {{-- Desktop Right Side: User Info & Logout (Hidden on mobile) --}}
                <div class="hidden md:flex md:items-center">
                    @auth
                        <span class="text-sm text-gray-500 mr-4 whitespace-nowrap">
                            Hi, {{ auth()->user()->name }}
                        </span>
                        
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-red-600 font-medium focus:outline-none transition-colors duration-150">
                                Logout
                            </button>
                        </form>
                    @endauth
                </div>

                {{-- Mobile Hamburger Button (Visible only on mobile) --}}
                <div class="flex items-center md:hidden">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="Toggle menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        {{-- Mobile Dropdown Menu (Hidden by default, toggled via JS) --}}
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 absolute w-full shadow-lg z-50">
            <div class="px-4 pt-2 pb-3 space-y-1">
                <a href="{{ route('awards.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50">Awards</a>
                <a href="{{ route('employees.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50">Employees</a>
                <a href="{{ route('attendance.timesheet') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50">Timesheets</a>
            </div>
            
            @auth
                <div class="pt-4 pb-4 border-t border-gray-200">
                    <div class="px-7 flex items-center justify-between">
                        <div class="text-base font-medium text-gray-800">
                            Hi, {{ auth()->user()->name }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="block text-gray-600 hover:text-red-600 font-medium focus:outline-none">
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

</body>
</html>