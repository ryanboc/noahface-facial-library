<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - NoahFace Sync</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Create an Account</h2>

        {{-- Display Validation Errors (like passwords not matching) --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.submit') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-bold mb-2 text-sm">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2 text-sm">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-bold mb-2 text-sm">Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 font-bold mb-2 text-sm">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors mb-4">
                Register
            </button>
            
            <div class="text-center mt-2">
                <span class="text-gray-600 text-sm">Already have an account?</span>
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 font-semibold ml-1">Login here.</a>
            </div>
        </form>
    </div>

</body>
</html>