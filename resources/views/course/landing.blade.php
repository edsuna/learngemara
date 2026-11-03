@extends('layouts.course')

@section('title', __('course.page_title'))

@push('head')
    <meta name="description" content="{{ __('course.meta_description') }}">
    <meta property="og:title" content="{{ __('course.page_title') }}">
    <meta property="og:description" content="{{ __('course.meta_description') }}">
@endpush

@section('main')
    <section class="py-8 sm:py-14 text-center">
        <h1 class="text-3xl sm:text-5xl font-semibold">{{ __('course.promise') }}</h1>
        <p class="mt-4 text-lg max-w-2xl mx-auto">{{ __('course.supporting') }}</p>

        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('course.enroll') }}" class="w-full sm:w-auto rounded-lg bg-oxblood px-8 py-3 font-semibold text-paper hover:opacity-90">{{ __('course.enroll') }}</a>
            <a href="{{ $toolLink }}" class="w-full sm:w-auto rounded-lg border-2 border-oak px-8 py-3 font-medium hover:bg-linen">{{ __('course.use_tool') }}</a>
        </div>

        <a href="{{ $exampleLink }}" class="mt-5 inline-block text-oxblood underline">{{ __('course.example_case') }}</a>
    </section>

    @if ($videoUrl)
        <section class="pb-10">
            <iframe src="{{ $videoUrl }}" title="{{ __('course.page_title') }}" class="w-full aspect-video rounded-lg" loading="lazy" allowfullscreen></iframe>
        </section>
    @endif

    <section class="py-8 border-t border-linen">
        <h2 class="text-2xl font-semibold">{{ __('course.how_heading') }}</h2>
        <ol class="mt-4 space-y-3 list-decimal list-inside text-lg">
            <li>{{ __('course.point_1') }}</li>
            <li>{{ __('course.point_2') }}</li>
            <li>{{ __('course.point_3') }}</li>
        </ol>
    </section>

    <section id="about-the-method" class="py-8 border-t border-linen scroll-mt-4">
        <h2 class="text-2xl font-semibold">{{ __('course.about_method_heading') }}</h2>
        <p class="mt-4 text-lg">{{ __('course.about_method_body') }}</p>
    </section>

    <section class="py-10 border-t border-linen text-center">
        <p class="text-2xl font-semibold">{{ __('course.closing') }}</p>
        <a href="{{ route('course.enroll') }}" class="mt-5 inline-block rounded-lg bg-oxblood px-8 py-3 font-semibold text-paper hover:opacity-90">{{ __('course.enroll') }}</a>
    </section>
@endsection
