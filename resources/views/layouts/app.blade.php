<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    </head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="text-xl font-bold text-blue-600">
                        Payroll System
                    </a>
                    <div class="ml-10 space-x-4">
                        <a href="{{ route('awards.index') }}" class="text-gray-600 hover:text-blue-600">Awards</a>
                        <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-blue-600">Employees</a>
                    </div>
                </div>
            </div>
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