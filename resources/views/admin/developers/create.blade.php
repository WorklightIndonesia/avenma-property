@extends('layouts.admin')

@section('title', 'Tambah developer')

@section('content')
    <h1 class="text-xl font-semibold text-ink-950">Tambah developer</h1>

    @include('admin.developers._form', [
        'action' => route('admin.developers.store'),
        'method' => 'POST',
    ])
@endsection
