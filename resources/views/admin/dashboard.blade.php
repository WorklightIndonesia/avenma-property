@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold text-slate-900">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-sm text-slate-500">Gunakan menu di samping untuk mengelola konten website.</p>
    </div>
@endsection
