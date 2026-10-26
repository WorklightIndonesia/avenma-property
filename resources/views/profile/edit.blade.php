@extends('layouts.admin')

@section('title', 'Profil')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <section class="border border-line bg-paper p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

        <section class="border border-line bg-paper p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </section>

        <section class="border border-line bg-paper p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </section>
    </div>
@endsection
