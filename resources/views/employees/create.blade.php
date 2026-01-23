@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Link New Employee</h1>
    @include('employees._form', ['awards' => $awards])
</div>
@endsection