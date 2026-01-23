@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Create New Award</h1>
    @include('awards.form')
</div>
@endsection