@extends('layouts.app')

@section('content')

    <div class="min-h-screen py-12 sm:px-6 lg:px-8">
        @livewire('top-bar', ['englishTitle' => 'How to Learn Gemara', 'hebrewTitle' => 'איך לומדים הלמדנים'])

        <div :class="{'rtl': selectedLanguage === 'Hebrew'} "
            @togglelanguage.window="toggleLanguage()"
        >
        <div class="max-w-screen-xl mx-auto">
            <div>
                We try to make learning Gemara accessible.
            </div>
            <div>
                <a href="{{ route('gemara_cases.create') }}">Analytic skillset tool</a>
            </div>
            <div>
                <a href="{{ route('gemara_cases.index', ['public' => 1]) }}">Public Gemara Cases</a>
            </div>
            @auth
            <div>
                <a href="{{ route('gemara_cases.index') }}">My Gemara Cases</a>
            </div>
            @endauth
        </div>
        </div>
    </div>

@endsection
