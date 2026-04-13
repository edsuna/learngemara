@extends('layouts.app')

@section('content')

    <div class="min-h-screen py-12 bg-[#faf6ed] sm:px-6 lg:px-8">
        @livewire('top-bar', ['englishTitle' => 'How to Learn Gemara', 'hebrewTitle' => 'איך לומדים הלמדנים'])

        <div :class="{'rtl': selectedLanguage === 'Hebrew'} "
            @togglelanguage.window="toggleLanguage()"
        >
        <div class="max-w-4xl mx-auto mt-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Analytic Skillset Tool --}}
                <a href="{{ route('gemara_cases.create') }}" class="bg-white rounded-md p-8 border-2 border-[#c7b299] hover:border-indigo-400 shadow-sm hover:shadow-md text-center transition duration-150 ease-in-out block">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="font-[Frank_Ruhl_Libre] text-xl font-bold text-stone-900">Analytic Skillset Tool</h3>
                    <p class="text-sm text-stone-500 mt-3">Build and analyze Talmudic cases with our structured learning tool.</p>
                </a>

                {{-- Public Gemara Cases --}}
                <a href="{{ route('gemara_cases.index', ['public' => 1]) }}" class="bg-white rounded-md p-8 border-2 border-[#c7b299] hover:border-indigo-400 shadow-sm hover:shadow-md text-center transition duration-150 ease-in-out block">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="font-[Frank_Ruhl_Libre] text-xl font-bold text-stone-900">Public Gemara Cases</h3>
                    <p class="text-sm text-stone-500 mt-3">Browse cases shared by the community for collaborative learning.</p>
                </a>

                {{-- My Gemara Cases --}}
                @auth
                <a href="{{ route('gemara_cases.index') }}" class="bg-white rounded-md p-8 border-2 border-[#c7b299] hover:border-indigo-400 shadow-sm hover:shadow-md text-center transition duration-150 ease-in-out block">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="font-[Frank_Ruhl_Libre] text-xl font-bold text-stone-900">My Gemara Cases</h3>
                    <p class="text-sm text-stone-500 mt-3">Access and manage your personal collection of cases.</p>
                </a>
                @endauth
            </div>
        </div>
        </div>
    </div>

@endsection
