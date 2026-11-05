@extends('layouts.admin')

@section('title', 'Ubah agen')

@section('content')
    <x-back-link :href="route('admin.agents.index')">Agen</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Ubah agen</h1>

    @include('admin.agents._form', [
        'action' => route('admin.agents.update', $agent),
        'method' => 'PUT',
    ])
@endsection
