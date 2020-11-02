@extends('layouts.app')

@section('content')

    <div class="flex flex-col justify-center min-h-screen py-12 bg-gray-50 sm:px-6 lg:px-8">
        @livewire('top-bar')

        <div class="flex items-center justify-center">
            <div class="flex justify-around w-full">
                <div class="space-y-6">
                    <a href="{{ route('home') }}">
                        <img class="mx-auto" src="{{ asset('storage/images/logo.png') }}">
                        {{-- <x-logo class="w-auto h-16 mx-auto text-indigo-600" /> --}}
                    </a>

                    <h1 class="text-5xl font-extrabold tracking-wider text-center text-gray-600" x-text>

                    </h1>
                </div>
            </div>
        </div>
        {!! Form::open() !!}
            <div x-data="gemaraCase()" x-init="getMasechtot()" :class="{'rtl': selectedLanguage === 'Hebrew'} " @togglelanguage.window="toggleLanguage()">
                <div  class="flex items-start ">
                    <div class="mr-2">
                        <lable for='masechet' class="mr-2" x-text="localizedTexts.masechetLabel"></lable>
                        <select x-model='selectedMasechet' @change='selectMasechet()'>
                            <option value="" x-text="localizedTexts.selectMasechet"></option>
                            <template x-for="masechet in masechtot">
                                <option :key="masechet.englishName" :value="masechet.englishName" x-text="(selectedLanguage === 'English') ? masechet.englishName.replace('_', ' ') : masechet.text">
                            </template>
                        </select>
                    </div>

                    <div class="mr-2" x-show="selectedMasechet">
                        <lable for='daf' class="mr-2" x-text="localizedTexts.masechetLabel"></lable>
                        <select x-model='selectedDaf' @change="amudText = ''">
                            <template x-for="daf in dapim">
                                <option :key="daf.value" :value="daf.value" x-text="daf.text">
                            </template>
                        </select>
                    </div>

                    <div x-show="selectedDaf">
                        <div class="flex">
                            <div>
                                <div
                                    class="cursor-pointer rounded-lg border-gray-400 border-2 p-2"
                                    @click="getAmudText()"
                                    x-text="localizedTexts.showAmudText"
                                >
                                </div>
                                @livewire('modal', ['showFlag' => 'amudText', 'modalContent' => 'amudText', 'clickAway' => 'amudText=false'])
                            </div>
                            <div>
                                <textarea rows="5" cols="20" x-model="selectedText" class="rtl"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {!! Form::close() !!}
    </div>

@endsection
