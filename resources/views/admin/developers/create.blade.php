@extends('layouts.admin')

@section('title', 'Tambah developer')

@section('content')
    <x-back-link :href="route('admin.developers.index')">Developer</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Tambah developer</h1>

    @include('admin.developers._form', [
        'action' => route('admin.developers.store'),
        'method' => 'POST',
    ])
@endsection
