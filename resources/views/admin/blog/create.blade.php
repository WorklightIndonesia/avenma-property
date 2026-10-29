@extends('layouts.admin')

@section('title', 'Tulis artikel')

@section('content')
    <x-back-link :href="route('admin.blog.index')">Blog</x-back-link>
    <h1 class="text-xl font-semibold text-ink-950">Tulis artikel</h1>
    <p class="mt-1 text-sm text-ink-500">Sampul bisa ditambahkan sekarang atau setelah artikel tersimpan.</p>

    @include('admin.blog._form', [
        'action' => route('admin.blog.store'),
        'method' => 'POST',
    ])
@endsection

@include('admin.blog._quill')
