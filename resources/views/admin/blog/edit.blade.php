@extends('layouts.admin')

@section('title', 'Edit artikel')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.blog.index')">Blog</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">{{ $post->title }}</h1>
        </div>

        @if ($post->is_published)
            <a href="{{ route('public.blog.show', $post) }}" class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand">
                Lihat di website
            </a>
        @endif
    </div>

    @include('admin.blog._form', [
        'action' => route('admin.blog.update', $post),
        'method' => 'PUT',
    ])
@endsection

@include('admin.blog._quill')
