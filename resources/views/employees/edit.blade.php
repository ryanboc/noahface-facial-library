@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Employee: {{ $employee->name }}</h1>
    @include('employees._form', ['employee' => $employee, 'awards' => $awards])
</div>
@endsection