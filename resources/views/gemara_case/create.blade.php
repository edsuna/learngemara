@extends('layouts.app')

@section('content')

    <div class="min-h-screen py-12 bg-blue-50 bg-opacity-25 sm:px-6 lg:px-8">
        @livewire('top-bar', ['englishTitle' => 'Analytic Skillset Tool', 'hebrewTitle' => 'כלי למיומנות אנליטית'])

        <div
            x-data="gemaraCase()"
            x-init="getMasechtot()"
            :class="{'rtl': selectedLanguage === 'Hebrew'} "
            @togglelanguage.window="toggleLanguage()"
            x-cloak
        >
            <form @submit.prevent="submitCase" method="POST">
                @csrf
                <div class="flex gemara-case max-w-screen-xl mx-auto"
                    :class="{'justify-between' : theCase.gemaraText}"
                >
                    <div class="flex">
                        <div class="">
                            <select x-show='!theCase.masechet' x-model='theCase.masechet' @change='selectMasechet()'>
                                <option value="" x-text="localizedTexts.selectMasechet"></option>
                                <template x-for="masechet in masechtot">
                                    <option :key="masechet.englishName" :value="masechet.englishName" x-text="(selectedLanguage === 'English') ? masechet.englishName.replace('_', ' ') : masechet.text">
                                </template>
                            </select>
                            @include('subviews.clickable-text',
                                     ['show' => 'theCase.masechet',
                                      'dblClick' => 'resetCase()',
                                      'text' => 'masechetName'])
                        </div>

                        <div class="" x-show="theCase.masechet">
                            {{-- <label for='daf' class="" x-text="localizedTexts.masechetLabel"></label> --}}
                            <select x-show="!theCase.daf" x-model='theCase.daf' @change="amudText = ''">
                                <template x-for="daf in dapim">
                                    <option :key="daf.value" :value="daf.value" x-text="daf.text">
                                </template>
                            </select>
                            @include('subviews.clickable-text',
                                     ['show' => 'theCase.daf',
                                      'dblClick' => 'resetDaf()',
                                      'text' => 'dafAsText'])
                        </div>

                        <div x-show="theCase.daf && !theCase.gemaraText" class="flex">
                            <textarea rows="2"
                                cols="20"
                                @blur="theCase.gemaraText = tmpSelectedText"
                                class="border-gray-400 border-2 rounded-sm focus:outline-none rtl"
                                x-model="tmpSelectedText">
                            </textarea>
                            <button @click="getAmudText()" type="button">
                                <img src="{{ asset('storage/images/amud-text.png')}}"
                                    class="h-8 w-8"
                                    :alt="localizedTexts.showAmudText"
                                    >
                            </button>
                            @include('subviews.modal',
                                     ['showFlag' => 'amudText',
                                      'modalContent' => 'amudText',
                                      'classes' => 'rtl',
                                      'clickAway' => 'amudText=false'])
                        </div>

                        <div x-show="theCase.gemaraText"
                            @dblclick="theCase.gemaraText=''"
                            class="rtl max-w-md border-b-2 border-dashed"
                            x-text="theCase.gemaraText">
                        </div>
                    </div>

                    <div x-show="theCase.gemaraText" class="flex">
                        <input type="text"
                            x-model="theCase.title"
                            class="pl-1 inline-block align-middle mr-4"
                            :placeholder="localizedTexts.titleLabel">
                        <div>
                            <input type="checkbox"
                                class="inline-block align-middle"
                                x-model="theCase.public">
                            <label class="inline-block align-middle">
                                Pubic
                            </label>
                        </div>
                    </div>
                </div>
                <div x-show="theCase.gemaraText"
                    class="ltr gc-diagram grid grid-cols-6 gap-x-8 gap-y-24 justify-items-center border-4 border-blue-500 p-4 mt-4 max-w-screen-xl mx-auto"
                >

@foreach ($inputConditions as $inputCondition)
                    <div class="gc-{{ $inputCondition }}"
                        :class="{'opacity-50 bg-opacity-25': theCase.inputConditions.{{ $inputCondition }}.notRelevant}"
                    >
                        <div class="diagram-ellipse overflow-hidden"
                            x-show="theCase.act && theCase.dinType"
                            id="gc-{{ $inputCondition }}"">
                            <div class="flex flex-col h-full justify-evenly mb-4 mx-2"
                                id="gc-{{ $inputCondition }}-inner">
                                <div class="flex justify-evenly items-center">
                                    <div x-html="localizedTexts.case{{ ucfirst($inputCondition) }}">
                                    </div>
                                    <div x-show="{{ $hideIcons ? 'false' : 'true' }}">
                                        <img src="{{ asset("storage/images/$inputCondition.png")}}"
                                            class="h-8 w-8"
                                            :alt="localizedTexts.case{{ ucfirst($inputCondition) }}"
                                        >
                                    </div>
                                </div>
                                <div x-show="!theCase.inputConditions.{{ $inputCondition }}.value" class="flex content-center items-center">
                                    <div class="w-full">
                                        <input type="text"
                                            class="w-11/12 border-2 text-center"
                                            x-model="tmpCase.{{ $inputCondition }}"
                                            @change="updateCase('{{ $inputCondition }}')">
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                            class="inline-block align-middle"
                                            x-model="theCase.inputConditions.{{ $inputCondition }}.notRelevant"
                                            @click="theCase.inputConditions.{{ $inputCondition }}.value = (!theCase.inputConditions.{{ $inputCondition }}.notRelevant ? ' ' : theCase.inputConditions.{{ $inputCondition }}.value)">
                                        <label class="inline-block align-middle">N/R</label>
                                    </div>
                                </div>
                                <div>
                                    @include('subviews.clickable-text',
                                             ['show' => "theCase.inputConditions.$inputCondition.value",
                                              'dblClick' => "resetCasePiece('$inputCondition')",
                                              'text' => "getInputConditionText('$inputCondition')"])
                                </div>
                            </div>
                        </div>
                    </div>
@endforeach
                    <div class="gc-act">
                        <div class="diagram-ellipse overflow-hidden flex flex-col justify-evenly h-full"
                            x-show="theCase.dinType"
                            id="gc-act"">
                            <div class="mx-24 h-1 -mt-2" id="gc-act-top"></div>
                            <div class="mx-12 h-1 mt-2" id="gc-act-inner"></div>
                            <div x-html="localizedTexts.caseAct"></div>
                            <div x-show="!theCase.act" class="flex content-center items-center">
                                <div class="w-3/4 mx-auto">
                                    <input type="text"
                                        class="w-11/12 border-2 text-center"
                                        x-model="tmpCase.act"
                                        @blur="updateAct()">
                                </div>
                            </div>
                            <div class="mb-4">
                                @include('subviews.clickable-text',
                                        ['show' => "theCase.act",
                                         'dblClick' => "resetAct()",
                                         'text' => "theCase.act"])
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="theCase.gemaraText">
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
                            <select x-show='!theCase.dinType' x-model='theCase.dinType''>
                                <option value="" x-text="localizedTexts.selectDinType"></option>
                                <template x-for="aDinType in dinTypes">
                                    <option :key="aDinType" :value="aDinType" x-text="aDinType">
                                </template>
                            </select>
                            @include('subviews.clickable-text',
                                    ['show' => "theCase.dinType",
                                     'dblClick' => "theCase.dinType = ''",
                                     'text' => "theCase.dinType"])
                        </div>
                    </div>
                </div>
                <div class="flex align-text-top justify-center mt-4"
                    x-show="theCase.act && theCase.dinType"
                >
                @auth
                    <button :disabled="!caseComplete()"
                        x-text="localizedTexts.saveCase"
                        :class="{'cursor-wait': !caseComplete()}"
                    ></button>
                    <div class="rounded-full bg-red-600 cursor-pointer text-sm w-5 h-5 text-center ml-4"
                        @click="showErrors = true"
                        x-show="!caseComplete()"
                        type="button">
                        ?
                    </div>
                    @include('subviews.modal',
                             ['showFlag' => 'showErrors',
                              'modalContent' => 'validationErrors',
                              'classes' => '',
                              'clickAway' => 'showErrors = false'])
                @else
                    <div x-text="localizedTexts.needLogin"></div>
                @endauth
                </div>

            </form>
        </div>
    </div>

@endsection
