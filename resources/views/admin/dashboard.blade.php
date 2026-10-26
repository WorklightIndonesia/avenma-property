@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="rounded border border-line bg-paper p-6">
        <h1 class="text-xl font-semibold text-ink-950">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-sm text-ink-500">Gunakan menu di samping untuk mengelola konten website.</p>
    </div>
@endsection
