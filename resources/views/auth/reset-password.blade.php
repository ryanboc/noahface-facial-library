<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md rounded bg-white p-8 shadow-md">
        <h1 class="text-center text-2xl font-bold text-gray-800">Choose a new password</h1>

        @if($errors->any())
            <div class="mt-5 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div><label for="email" class="mb-2 block font-bold text-gray-700">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" class="w-full rounded border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label for="password" class="mb-2 block font-bold text-gray-700">New password</label><input id="password" name="password" type="password" required autocomplete="new-password" class="w-full rounded border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label for="password_confirmation" class="mb-2 block font-bold text-gray-700">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <button class="w-full rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-600">Reset password</button>
        </form>
    </div>
</body>
</html>
