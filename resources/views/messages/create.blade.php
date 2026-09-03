@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">SMS</p>
    <h1 class="mb-6 text-3xl font-bold">Create message template</h1>
    @include('messages._form', ['message' => null])
</div>
@endsection
