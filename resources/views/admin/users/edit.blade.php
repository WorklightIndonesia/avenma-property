@extends('layouts.admin')

@section('title', 'Ubah akun')

@section('content')
    <h1 class="text-xl font-semibold text-ink-950">Ubah akun</h1>

    @include('admin.users._form', [
        'action' => route('admin.users.update', $user),
        'method' => 'PUT',
    ])
@endsection
