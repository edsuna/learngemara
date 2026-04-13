@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-stone-50">
    @livewire('top-bar', ['englishTitle' => 'How to Learn Gemara', 'hebrewTitle' => 'איך לומדים הלמדנים'])

    <div :class="{'rtl': selectedLanguage === 'Hebrew'}"
        @togglelanguage.window="toggleLanguage()"
    >
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center py-16 sm:py-20">
            <p class="text-lg text-stone-600 max-w-2xl mx-auto leading-relaxed">
                We try to make learning Gemara accessible.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pb-20">
            <a href="{{ route('gemara_cases.create') }}"
                class="group block bg-white rounded-xl p-8 shadow-sm hover:shadow-md border border-stone-200 hover:border-teal-300 transition-all duration-300">
                <div class="w-12 h-12 bg-teal-50 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-stone-800 group-hover:text-teal-700 transition-colors">
                    Analytic Skillset Tool
                </h3>
                <p class="text-sm text-stone-500 mt-2">
                    Break down a Gemara case into its analytical components
                </p>
            </a>

            <a href="{{ route('gemara_cases.index', ['public' => 1]) }}"
                class="group block bg-white rounded-xl p-8 shadow-sm hover:shadow-md border border-stone-200 hover:border-teal-300 transition-all duration-300">
                <div class="w-12 h-12 bg-teal-50 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-stone-800 group-hover:text-teal-700 transition-colors">
                    Public Gemara Cases
                </h3>
                <p class="text-sm text-stone-500 mt-2">
                    Browse cases shared by the community
                </p>
            </a>

            @auth
            <a href="{{ route('gemara_cases.index') }}"
                class="group block bg-white rounded-xl p-8 shadow-sm hover:shadow-md border border-stone-200 hover:border-teal-300 transition-all duration-300">
                <div class="w-12 h-12 bg-teal-50 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-stone-800 group-hover:text-teal-700 transition-colors">
                    My Gemara Cases
                </h3>
                <p class="text-sm text-stone-500 mt-2">
                    View and manage your saved cases
                </p>
            </a>
            @endauth
        </div>
    </div>
    </div>
</div>

@endsection
