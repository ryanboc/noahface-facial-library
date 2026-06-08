@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12 flex justify-center">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-lg">
        
        <h2 class="text-2xl font-bold mb-4 text-center text-gray-800">Set Up 2FA</h2>
        <p class="text-gray-600 text-center mb-6">
            Open your authenticator app (like Google Authenticator or Authy) and scan the QR code below.
        </p>

        {{-- Display the generated QR Code --}}
        <div class="flex justify-center mb-6 border p-4 rounded bg-gray-50">
            {!! $qrCodeSvg !!}
        </div>

        <p class="text-sm text-center text-gray-500 mb-6">
            If you cannot scan the code, enter this key manually:<br>
            <code class="font-bold text-gray-800 bg-gray-200 px-2 py-1 rounded">{{ $secret }}</code>
        </p>

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Verification Form --}}
        <form action="{{ route('2fa.enable') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="pin" class="block text-gray-700 font-bold mb-2 text-center">Enter 6-Digit Code</label>
                <input type="text" name="pin" id="pin" required autofocus maxlength="6"
                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-xl tracking-widest">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Verify and Enable
            </button>
        </form>

    </div>
</div>
@endsection