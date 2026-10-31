@extends('layouts.public')

@section('meta')
    <x-meta
        title="Blog"
        description="Artikel tentang membeli, menjual, dan merawat properti dari Avenma Properti."
    />
@endsection

@section('content')
    <section class="bg-paper">
        <div class="mx-auto max-w-[90rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <h1 class="text-3xl font-semibold tracking-tight">Blog</h1>
            <p class="mt-1 text-sm text-ink-500">Panduan singkat untuk membeli, menjual, dan merawat properti.</p>

            <form
                id="blog-search-form"
                method="GET"
                action="{{ route('public.blog.index') }}"
                class="mt-6 flex flex-wrap items-center gap-2"
                x-data="{ timer: null, search() { clearTimeout(this.timer); this.timer = setTimeout(() => document.getElementById('blog-search-form').requestSubmit(), 1000); } }"
            >
                <label for="blog-search" class="sr-only">Cari artikel</label>
                <input
                    id="blog-search"
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari artikel…"
                    class="min-h-11 w-full max-w-sm rounded border-line bg-white py-2 text-sm text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30"
                    x-on:input="search()"
                >
                <x-primary-button>Cari</x-primary-button>

                @if ($search !== '')
                    <a href="{{ route('public.blog.index') }}" class="inline-flex min-h-11 items-center px-3 text-sm font-semibold text-ink-600 hover:text-ink-950">Hapus</a>
                @endif
            </form>

            @if ($posts->isEmpty())
                <div class="mt-8 border border-line p-10 text-center">
                    <p class="font-semibold">{{ $search !== '' ? 'Tidak ada artikel yang cocok.' : 'Belum ada artikel.' }}</p>
                    <p class="mt-2 text-sm text-ink-600">{{ $search !== '' ? 'Coba kata kunci lain atau lihat semua artikel.' : 'Artikel baru akan tampil di sini.' }}</p>
                </div>
            @else
                @php($items = collect($posts->items()))

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items->take(3) as $post)
                        @php($date = ($post->published_at ?? $post->created_at)->locale('id')->translatedFormat('j F Y'))

                        <article class="flex flex-col border border-line bg-white">
                            @if ($post->cover_image)
                                <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="aspect-[16/9] w-full object-cover" loading="lazy">
                            @endif

                            <div class="flex flex-1 flex-col p-5">
                                <p class="text-xs text-ink-500">{{ $date }}@if ($post->creator) · {{ $post->creator->name }}@endif</p>

                                <h2 class="mt-2 text-lg font-semibold leading-snug">
                                    <a href="{{ route('public.blog.show', $post) }}">{{ $post->title }}</a>
                                </h2>

                                @if (filled($post->excerpt))
                                    <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $post->excerpt }}</p>
                                @endif

                                <a href="{{ route('public.blog.show', $post) }}" class="mt-auto inline-block self-start pt-4 text-sm font-semibold text-ink-950 transition duration-150 hover:scale-105 hover:underline motion-reduce:transition-none motion-reduce:hover:scale-100">Baca Artikel →</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($items->count() > 3)
                    <div class="mt-10 divide-y divide-line border-t border-line">
                        @foreach ($items->slice(3) as $post)
                            @php($date = ($post->published_at ?? $post->created_at)->locale('id')->translatedFormat('j F Y'))

                            <article class="flex flex-col gap-4 py-6 sm:flex-row sm:items-start sm:gap-6">
                                @if ($post->cover_image)
                                    <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="aspect-[16/9] w-full shrink-0 object-cover sm:w-64" loading="lazy">
                                @endif

                                <div class="min-w-0">
                                    <p class="text-xs text-ink-500">{{ $date }}@if ($post->creator) · {{ $post->creator->name }}@endif</p>

                                    <h3 class="mt-1 text-lg font-semibold leading-snug">
                                        <a href="{{ route('public.blog.show', $post) }}">{{ $post->title }}</a>
                                    </h3>

                                    @if (filled($post->excerpt))
                                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-ink-600">{{ $post->excerpt }}</p>
                                    @endif

                                    <a href="{{ route('public.blog.show', $post) }}" class="mt-2 inline-block text-sm font-semibold text-ink-950 transition duration-150 hover:scale-105 hover:underline motion-reduce:transition-none motion-reduce:hover:scale-100">Baca Artikel →</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div class="mt-8">
                    <x-pagination :paginator="$posts" />
                </div>
            @endif
        </div>
    </section>
@endsection
