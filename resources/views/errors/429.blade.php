@extends('errors.layout')

@section('code', '429')
@section('heading', 'Terlalu banyak permintaan')
@section('body', 'Terlalu banyak percobaan dalam waktu singkat. Tunggu satu menit, lalu coba lagi.')

@section('actions')
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
        Kembali ke beranda
    </a>
@endsection
