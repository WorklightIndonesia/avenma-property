@extends('layouts.admin')

@section('title', 'Tambah akun')

@section('content')
    <h1 class="text-xl font-semibold text-ink-950">Tambah akun</h1>

    @include('admin.users._form', [
        'action' => route('admin.users.store'),
        'method' => 'POST',
    ])
@endsection
