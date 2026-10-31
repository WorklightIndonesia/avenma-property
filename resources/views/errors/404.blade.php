@extends('errors.layout')

@section('code', '404')
@section('heading', 'Halaman tidak ditemukan')
@section('body', 'Alamat ini tidak ada, atau properti yang Anda cari sudah tidak tayang.')

@section('actions')
    <a href="{{ url('/properti') }}" class="inline-flex items-center justify-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
        Lihat properti
    </a>
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-sand focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
        Beranda
    </a>
@endsection
