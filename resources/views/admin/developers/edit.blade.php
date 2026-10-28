@extends('layouts.admin')

@section('title', 'Ubah developer')

@section('content')
    <h1 class="text-xl font-semibold text-ink-950">Ubah developer</h1>

    @include('admin.developers._form', [
        'action' => route('admin.developers.update', $developer),
        'method' => 'PUT',
    ])
@endsection
