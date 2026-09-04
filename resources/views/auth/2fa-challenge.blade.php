<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md rounded bg-white p-8 shadow-md">
        <h1 class="text-center text-2xl font-bold text-gray-800">Authentication code</h1>
        <p class="mt-2 text-center text-sm text-gray-600">Enter the 6-digit code from your authenticator app.</p>

        @if ($errors->any())
            <div class="mt-5 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('2fa.verify') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="pin" class="mb-2 block font-bold text-gray-700">6-digit code</label>
                <input id="pin" name="pin" type="text" required autofocus inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" class="w-full rounded border px-3 py-2 text-center text-xl tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button class="w-full rounded bg-blue-600 px-4 py-2 font-bold text-white hover:bg-blue-700">Verify and sign in</button>
        </form>

        <div class="mt-5 border-t pt-4 text-center text-sm text-gray-600">
            Lost access to your authenticator?
            <a href="{{ route('password.request') }}" class="font-semibold text-blue-600 hover:text-blue-800">Reset it by email</a>
        </div>
    </div>
</body>
</html>
