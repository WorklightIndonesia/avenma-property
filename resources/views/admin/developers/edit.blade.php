@extends('layouts.admin')

@section('title', 'Ubah developer')

@section('content')
    <x-back-link :href="route('admin.developers.index')">Developer</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Ubah developer</h1>

    @include('admin.developers._form', [
        'action' => route('admin.developers.update', $developer),
        'method' => 'PUT',
    ])
@endsection
