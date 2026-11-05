@extends('layouts.public')

@section('meta')
    <x-meta
        :title="$post->title"
        :description="\Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->content), 155)"
        :image="$post->cover_image ? asset($post->cover_image) : null"
        type="article"
    />
@endsection

@push('head')
    <script type="application/ld+json" nonce="{{ $cspNonce }}">
        {!! json_encode([
            '@'.'context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('public.pages.home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('public.blog.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
    @php($date = ($post->published_at ?? $post->created_at)->locale('id')->translatedFormat('j F Y'))

    <article class="bg-paper">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <nav aria-label="Breadcrumb" class="text-xs text-ink-500">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('public.pages.home') }}" class="hover:text-ink-950">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('public.blog.index') }}" class="hover:text-ink-950">Blog</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="font-medium text-ink-950">{{ $post->title }}</li>
                </ol>
            </nav>

            <h1 class="mt-4 text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ $post->title }}</h1>

            <p class="mt-3 text-sm text-ink-500">{{ $date }}</p>

            @if ($post->cover_image)
                <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="mt-6 aspect-[16/9] w-full border border-line object-cover">
            @endif

            @if (filled($post->excerpt))
                <p class="mt-6 border-l border-line pl-4 text-lg leading-snug text-ink-600">{{ $post->excerpt }}</p>
            @endif

            <div class="rich-text mt-6">{!! $post->content !!}</div>

            <div class="mt-10 border-t border-line pt-6">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-500">Ditulis oleh tim Avenma Properti</p>
                <p class="mt-2 max-w-prose text-sm leading-6 text-ink-600">
                    Kami mendampingi jual beli, sewa, dan pengelolaan properti di Jabodetabek. Pertanyaan tentang artikel ini bisa langsung dikirim ke tim kami.
                    <a href="{{ route('public.pages.contact') }}" class="font-medium text-ink-950 underline decoration-line underline-offset-4 hover:text-gold-700">Hubungi tim kami</a>.
                </p>
            </div>
        </div>
    </article>
@endsection
