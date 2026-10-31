@extends('errors.layout')

@section('code', '403')
@section('heading', 'Anda tidak punya akses ke halaman ini')
@section('body', 'Halaman ini hanya untuk akun dengan izin yang sesuai. Masuk dengan akun lain, atau kembali ke beranda.')

@section('actions')
    <a href="{{ url('/login') }}" class="inline-flex items-center justify-center rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
        Masuk
    </a>
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded border border-line px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-sand focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700">
        Beranda
    </a>
@endsection
