@extends('layouts.admin')

@section('title', 'Edit listing')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-back-link :href="route('admin.listings.index')">Listing</x-back-link>
            <h1 class="text-xl font-semibold text-ink-950">{{ $listing->title }}</h1>
        </div>

        @if ($listing->is_published)
            <a href="{{ route('public.listings.show', $listing) }}" class="inline-flex min-h-11 items-center rounded border border-line px-4 text-sm font-semibold text-ink-950 hover:bg-sand">
                Lihat di website
            </a>
        @endif
    </div>

    @include('admin.listings._form', [
        'action' => route('admin.listings.update', $listing),
        'method' => 'PUT',
    ])
@endsection
