@extends('layouts.public')

@section('title', 'Blog — Avenma Properti')
@section('meta_description', 'Artikel tentang membeli, menjual, dan merawat properti dari Avenma Properti.')

@section('content')
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <h1 class="text-3xl font-semibold tracking-tight">Blog</h1>
            <p class="mt-1 text-sm text-ink-500">Panduan singkat untuk membeli, menjual, dan merawat properti.</p>

            @if ($posts->isEmpty())
                <div class="mt-8 border border-line p-10 text-center">
                    <p class="font-semibold">Belum ada artikel.</p>
                    <p class="mt-2 text-sm text-ink-600">Artikel baru akan tampil di sini.</p>
                </div>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        @php($date = ($post->published_at ?? $post->created_at)->locale('id')->translatedFormat('j F Y'))

                        <article class="relative flex flex-col border border-line bg-white transition duration-150 hover:-translate-y-1 hover:border-ink-950 motion-reduce:transition-none motion-reduce:hover:translate-y-0">
                            <div class="flex aspect-[16/9] items-center justify-center bg-sand">
                                @if ($post->cover_image)
                                    <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <img src="{{ asset('images/brand/avenma-logo-mark-112.png') }}" alt="" width="56" height="40" class="h-10 w-auto" onerror="this.remove()">
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                <p class="text-xs text-ink-500">{{ $date }}</p>

                                <h2 class="mt-2 text-lg font-semibold leading-snug">
                                    <a href="{{ route('public.blog.show', $post) }}" class="after:absolute after:inset-0">{{ $post->title }}</a>
                                </h2>

                                @if (filled($post->excerpt))
                                    <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $post->excerpt }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    <x-pagination :paginator="$posts" />
                </div>
            @endif
        </div>
    </section>
@endsection
