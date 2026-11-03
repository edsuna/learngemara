@extends('layouts.base')

@section('body')
    <div class="min-h-screen bg-paper text-ink">
        <nav class="bg-linen">
            <div class="max-w-4xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-x-6 gap-y-2">
                <a href="{{ route('course.landing') }}">
                    <img src="{{ asset('images/course-logo.png') }}" alt="{{ config('app.name') }}" class="h-20 w-auto">
                </a>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm font-medium">
                    <a href="#about-the-method" class="text-oxblood hover:underline">{{ __('course.about_method_link') }}</a>

                    @if (config('course.contact_email'))
                        <a href="mailto:{{ config('course.contact_email') }}" class="text-oxblood hover:underline">{{ __('course.contact') }}</a>
                    @endif

                    @auth
                        <span>{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="cursor-pointer font-medium text-oxblood hover:underline">{{ __('course.sign_out') }}</button>
                        </form>
                    @else
                        <a href="{{ route('course.signin') }}" class="text-oxblood hover:underline">{{ __('course.sign_in') }}</a>
                    @endauth
                </div>
            </div>
        </nav>

        @if (session('status'))
            <div role="status" class="bg-oxblood text-paper text-sm text-center px-4 py-2">{{ session('status') }}</div>
        @endif

        <main class="max-w-4xl mx-auto px-4">
            @yield('main')
        </main>
    </div>
@endsection
