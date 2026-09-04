<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md rounded bg-white p-8 shadow-md">
        <h1 class="text-center text-2xl font-bold text-gray-800">Reset your password</h1>
        <p class="mt-2 text-center text-sm text-gray-600">Enter the email address used for your account and we’ll send you a secure reset link.</p>

        @if(session('status'))
            <div class="mt-5 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @error('email')
            <div class="mt-5 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('password.email') }}" class="mt-6">
            @csrf
            <label for="email" class="mb-2 block font-bold text-gray-700">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full rounded border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button class="mt-5 w-full rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-600">Email reset link</button>
        </form>

        <div class="mt-5 text-center"><a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Back to sign in</a></div>
    </div>
</body>
</html>
