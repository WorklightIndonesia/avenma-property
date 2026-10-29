@extends('layouts.public')

@section('title', $post->title.' — Avenma Properti')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->content), 155))

@section('content')
    @php($date = ($post->published_at ?? $post->created_at)->locale('id')->translatedFormat('j F Y'))

    <article class="bg-paper">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <a href="{{ route('public.blog.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-ink-600 underline-offset-4 hover:text-ink-950 hover:underline">
                ← Semua artikel
            </a>

            <h1 class="mt-4 text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ $post->title }}</h1>

            <p class="mt-3 text-sm text-ink-500">
                {{ $date }}
                @if ($post->creator)
                    <span> · {{ $post->creator->name }}</span>
                @endif
            </p>

            @if ($post->cover_image)
                <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="mt-6 aspect-[16/9] w-full border border-line object-cover">
            @endif

            @if (filled($post->excerpt))
                <p class="mt-6 border-l border-line pl-4 text-lg leading-snug text-ink-600">{{ $post->excerpt }}</p>
            @endif

            <div class="rich-text mt-6">{!! $post->content !!}</div>
        </div>
    </article>
@endsection
