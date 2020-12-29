@extends('layouts.app')

@section('content')
<div class="min-h-screen py-12 bg-blue-50 bg-opacity-25 sm:px-6 lg:px-8">
    @livewire('top-bar', ['englishTitle' => 'My Gemara Cases', 'hebrewTitle' => 'רשימת המקרים שלי'])

    @livewire('gemara-cases')
</div>
@endsection
