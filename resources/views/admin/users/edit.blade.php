@extends('layouts.admin')

@section('title', 'Ubah akun')

@section('content')
    <x-back-link :href="route('admin.users.index')">Akun</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Ubah akun</h1>

    @include('admin.users._form', [
        'action' => route('admin.users.update', $user),
        'method' => 'PUT',
    ])
@endsection
