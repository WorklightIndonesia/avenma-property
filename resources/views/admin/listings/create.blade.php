@extends('layouts.admin')

@section('title', 'Tambah listing')

@section('content')
    <x-back-link :href="route('admin.listings.index')">Listing</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Tambah listing</h1>
    <p class="mt-1 text-sm text-ink-500">Label tiap foto diisi setelah listing tersimpan.</p>

    @include('admin.listings._form', [
        'action' => route('admin.listings.store'),
        'method' => 'POST',
    ])
@endsection
