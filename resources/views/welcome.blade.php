@extends('layouts.app')

@section('content')

    <div class="min-h-screen py-12 bg-blue-50 bg-opacity-25 sm:px-6 lg:px-8">
        @livewire('top-bar', ['englishTitle' => 'Analytic Skillset Tool', 'hebrewTitle' => 'כלי למיומנות אנליטית'])

        <div
            x-data="gemaraCase()"
            x-init="getMasechtot()"
            :class="{'rtl': selectedLanguage === 'Hebrew'} "
            @togglelanguage.window="toggleLanguage()"
        >
            {!! Form::open() !!}
                <div class="flex gemara-case max-w-screen-xl mx-auto"
                    :class="{'justify-between' : selectedText}"
                >
                    <div class="flex">
                        <div class="">
                            {{-- <label for='masechet' class="" x-text="localizedTexts.masechetLabel"></label> --}}
                            <select x-show='!selectedMasechet' x-model='selectedMasechet' @change='selectMasechet()'>
                                <option value="" x-text="localizedTexts.selectMasechet"></option>
                                <template x-for="masechet in masechtot">
                                    <option :key="masechet.englishName" :value="masechet.englishName" x-text="(selectedLanguage === 'English') ? masechet.englishName.replace('_', ' ') : masechet.text">
                                </template>
                            </select>
                            <span x-show="selectedMasechet"
                                @dblclick="resetCase()"
                                class="border-b-2 border-dashed"
                                x-text="masechetName"></span>
                        </div>

                        <div class="" x-show="selectedMasechet">
                            {{-- <label for='daf' class="" x-text="localizedTexts.masechetLabel"></label> --}}
                            <select x-show="!selectedDaf" x-model='selectedDaf' @change="amudText = ''">
                                <template x-for="daf in dapim">
                                    <option :key="daf.value" :value="daf.value" x-text="daf.text">
                                </template>
                            </select>
                            <span x-show="selectedDaf"
                                @dblclick="resetDaf()"
                                class="border-b-2 border-dashed"
                                x-text="dafAsText"></span>
                        </div>

                        <div x-show="selectedDaf && !selectedText" class="flex">
                            <textarea rows="2"
                                cols="20"
                                @blur="selectedText = tmpSelectedText"
                                class="border-gray-400 border-2 rounded-sm focus:outline-none rtl"
                                x-model="tmpSelectedText">
                            </textarea>
                            <button @click="getAmudText()" type="button">
                                <img src="{{ asset('storage/images/amud-text.png')}}"
                                    class="h-8 w-8"
                                    x-bind:alt="localizedTexts.showAmudText"
                                    >
                            </button>
                            @livewire('modal', ['showFlag' => 'amudText', 'modalContent' => 'amudText', 'clickAway' => 'amudText=false'])
                        </div>

                        <div x-show="selectedText"
                            @dblclick="selectedText=''"
                            class="rtl max-w-md border-b-2 border-dashed"
                            x-text="selectedText">
                        </div>
                    </div>

                    <div x-show="selectedText">
                        <input type="text"
                            x-model="caseTitle"
                            class="pl-1"
                            x-bind:placeholder="localizedTexts.titleLabel">
                    </div>
                </div>
                <div x-show="selectedText" class="ltr gc-diagram grid grid-cols-6 gap-x-8 gap-y-24 justify-items-center border-4 border-blue-500 p-4 mt-4 max-w-screen-xl mx-auto">

@php
$pieces = [
    "consequences",
    "when",
    "where",
    "toWhat",
    "withWhat",
    "how",
    "other",
    "act",
    "who",
];
@endphp

@foreach ($pieces as $piece)
                    <div class="gc-{{ $piece }}"
                        :class="{'opacity-50 bg-opacity-25': notRelevant.{{ $piece }}}"
                    >
                        <div class="diagram-ellipse overflow-hidden"
                            x-show="(('{{ $piece }}' === 'act' && dinType) || ('{{ $piece }}' !== 'act' && gCase.act))"
                            :class="{'pt-4' : '{{ $piece }}' === 'act'}"
                            id="gc-{{ $piece }}"">
                            <div class="flex flex-col h-full justify-evenly mb-4 {{ ($piece == 'act') ? 'mx-12': 'mx-2' }}"
                                id="gc-{{ $piece }}-inner">
                                <div class="flex justify-evenly items-center">
                                    <div x-html="localizedTexts.case{{ ucfirst($piece) }}">
                                    </div>
@if ($piece !== 'act')
                                    <div x-show="{{ $hideIcons ? 'false' : 'true' }}">
                                        <img src="{{ asset("storage/images/$piece.png")}}"
                                            class="h-8 w-8"
                                            x-bind:alt="localizedTexts.case{{ ucfirst($piece) }}"
                                        >
                                    </div>
@endif

                                </div>
                                <div x-show="!gCase.{{ $piece }}" class="flex content-center items-center">
                                    <div class="w-full">
                                        <input type="text"
                                            class="{{ $piece == 'act' ? 'w-full' : 'w-11/12'}} border-2 text-center"
                                            x-model="tmpCase.{{ $piece }}"
                                            @blur="updateCase('{{ $piece }}')">
                                    </div>
                                    <div x-show="'{{ $piece }}' !== 'act'" class="flex items-center">
                                        <input type="checkbox"
                                            x-model="notRelevant.{{ $piece }}"
                                            @click="gCase.{{ $piece }} = (!notRelevant.{{ $piece }} ? ' ' : gCase.{{ $piece }})">
                                        N/R
                                    </div>
                                </div>
                                <div>
                                    <span x-text="notRelevant.{{ $piece }} ? localizedTexts.notRelevant : gCase.{{ $piece }}"
                                        x-show="gCase.{{ $piece }}"
                                        class="border-b-2 border-dashed"
                                        @dblclick="resetCasePiece('{{ $piece }}')"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    </div>

@endforeach
                </div>
                <div x-show="selectedText">
                    <div class="flex justify-center p-0 my-0">
                        <div class="w-2 bg-blue-500 h-24 -mt-5"></div>
                    </div>
                    <div class="flex justify-center">
                        <div class="arrow-head"></div>
                    </div>
                    <div
                        class="gc-din-type w-1/4 mx-auto border-blue-500 bg-blue-400 h-16 -mt-4 text-xs flex flex-col justify-center items-center">
                        <div x-text="localizedTexts.caseDin"></div>
                        <div>
                            <select x-show='!dinType' x-model='dinType''>
                                <option value="" x-text="localizedTexts.selectDinType"></option>
                                <template x-for="aDinType in dinTypes">
                                    <option :key="aDinType" :value="aDinType" x-text="aDinType">
                                </template>
                            </select>
                            <span x-show="dinType"
                                @dblclick="dinType = ''"
                                class="border-b-2 border-dashed"
                                x-text="dinType"></span>
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>

@endsection
