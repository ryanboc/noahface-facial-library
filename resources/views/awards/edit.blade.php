@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Award: {{ $award->name }}</h1>
    {{-- Pass the $award variable to the partial --}}
    @include('awards.form', ['award' => $award])
</div>
@endsection