@extends('layouts.admin')

@section('title', 'Tambah agen')

@section('content')
    <x-back-link :href="route('admin.agents.index')">Agen</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Tambah agen</h1>

    @include('admin.agents._form', [
        'action' => route('admin.agents.store'),
        'method' => 'POST',
    ])
@endsection
