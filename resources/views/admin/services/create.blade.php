@extends('layouts.admin')

@section('title', 'Tambah layanan')

@section('content')
    <x-back-link :href="route('admin.services.index')">Layanan</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Tambah layanan</h1>

    @include('admin.services._form', [
        'action' => route('admin.services.store'),
        'method' => 'POST',
    ])
@endsection
