@extends('layouts.app')
@section('content')<div class="container mx-auto px-4 py-8"><h1 class="text-3xl font-bold mb-6">Edit company</h1>@include('companies._form', ['company' => $company])</div>@endsection
