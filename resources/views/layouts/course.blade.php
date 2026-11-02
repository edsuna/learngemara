@extends('layouts.base')

@section('body')
    <nav class="bg-blue-50">
        <div class="max-w-4xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-x-6 gap-y-2">
            <a href="{{ route('course.landing') }}">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto">
            </a>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm font-medium">
                <a href="#about-the-method" class="text-indigo-600 hover:text-indigo-500">{{ __('course.about_method_link') }}</a>

                @if (config('course.contact_email'))
                    <a href="mailto:{{ config('course.contact_email') }}" class="text-indigo-600 hover:text-indigo-500">{{ __('course.contact') }}</a>
                @endif

                @auth
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="cursor-pointer font-medium text-indigo-600 hover:text-indigo-500">{{ __('course.sign_out') }}</button>
                    </form>
                @else
                    <a href="{{ route('course.signin') }}" class="text-indigo-600 hover:text-indigo-500">{{ __('course.sign_in') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    @if (session('status'))
        <div role="status" class="bg-indigo-600 text-white text-sm text-center px-4 py-2">{{ session('status') }}</div>
    @endif

    <main class="max-w-4xl mx-auto px-4">
        @yield('main')
    </main>
@endsection
