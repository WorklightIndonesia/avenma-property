@extends('layouts.admin')

@section('title', 'Ubah layanan')

@section('content')
    <x-back-link :href="route('admin.services.index')">Layanan</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Ubah layanan</h1>

    @include('admin.services._form', [
        'action' => route('admin.services.update', $service),
        'method' => 'PUT',
    ])
@endsection
