@extends('layouts.admin')

@section('title', 'Tambah listing')

@section('content')
    <x-back-link :href="route('admin.listings.index')">Listing</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Tambah listing</h1>
    <p class="mt-1 text-sm text-ink-500">Label tiap foto diisi setelah listing tersimpan.</p>

    @if ($submission ?? null)
        <p class="mt-2 border-l-2 border-gold-700 bg-gold-500/10 px-3 py-2 text-sm text-ink-600">
            Data diambil dari pengajuan titip properti {{ $submission->owner_name }} ({{ $submission->phone }}). Lengkapi wilayah, deskripsi, dan foto sebelum menerbitkan.
        </p>
    @endif

    @include('admin.listings._form', [
        'action' => route('admin.listings.store'),
        'method' => 'POST',
    ])
@endsection
