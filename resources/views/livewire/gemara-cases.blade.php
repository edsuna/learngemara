<div>
    <div
        x-data="gemaraCase()"
        x-init="getMasechtot('', false).then(() => { theCase.masechet = $wire.masechet || ''; theCase.daf = $wire.daf || ''; theCase.searchText = $wire.searchText || ''; if (theCase.masechet) selectMasechet(); })"
        @togglelanguage.window="toggleLanguage()"
        :class="{'rtl': selectedLanguage === 'Hebrew'}"
        class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8"
    >
        <div wire:ignore class="flex mb-8">
            <div class="mx-4">
                <select
                    x-model="theCase.masechet"
                    @change="if ($el.value) { selectMasechet(); } theCase.daf = ''; $nextTick(() => { $wire.set('masechet', $el.value); $wire.set('daf', '') })">
                    <option value="" x-text="localizedTexts.selectMasechet"></option>
                    <template x-for="masechet in masechtot">
                        <option :key="masechet.englishName" :value="masechet.englishName" x-text="(selectedLanguage === 'English') ? masechet.englishName.replace('_', ' ') : masechet.text">
                    </template>
                </select>
            </div>
            <div class="mx-4">
                <select x-model="theCase.daf"
                    @change="$nextTick(() => { $wire.set('daf', $el.value) })">
                    <option value="" x-text="localizedTexts.selectDaf"></option>
                    <template x-for="daf in dapim.filter(d => d.value !== '')">
                        <option :key="daf.value" :value="daf.value" x-text="daf.text">
                    </template>
                </select>
            </div>
            <div class="mx-4">
                <input type="text"
                    class="p-1 border-gray-500 border-2"
                    x-model="theCase.searchText"
                    @input.debounce.300ms="$wire.set('searchText', $el.value)"
                    :placeholder="localizedTexts.searchText">
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="hidden sm:flex justify-between px-6 py-3 bg-stone-50 border-b border-stone-200">
                <div class="flex-1 text-xs font-semibold text-stone-500 uppercase tracking-wider" x-text="localizedTexts.masechetLabel"></div>
                <div class="flex-1 text-xs font-semibold text-stone-500 uppercase tracking-wider" x-text="localizedTexts.dafLabel"></div>
                <div class="flex-1 text-xs font-semibold text-stone-500 uppercase tracking-wider" x-text="localizedTexts.titleLabel"></div>
                <div class="flex-1 text-xs font-semibold text-stone-500 uppercase tracking-wider" x-text="localizedTexts.caseDin"></div>
                <div class="flex-1 text-xs font-semibold text-stone-500 uppercase tracking-wider" x-text="localizedTexts.caseAct"></div>
                <div class="flex-1 text-xs font-semibold text-stone-500 uppercase tracking-wider" x-text="localizedTexts.text"></div>
                <div class="w-28 text-xs font-semibold text-stone-500 uppercase tracking-wider" x-text="localizedTexts.actions"></div>
            </div>

            @foreach ($gemaraCases as $case)
            <div class="flex flex-col sm:flex-row justify-between px-6 py-4 border-b border-stone-100 hover:bg-stone-50 transition-colors duration-150 case-row">
                <div class="flex-1 text-sm text-stone-800" x-show="selectedLanguage !== 'Hebrew'">
                    {{ str_replace('_', ' ', $case->masechet) }}
                </div>
                <div class="flex-1 text-sm text-stone-800" x-show="selectedLanguage === 'Hebrew'">
                    {{ $case->name }}
                </div>
                <div class="flex-1 text-sm text-stone-600">{{ $case->daf }}</div>
                <div class="flex-1 text-sm text-stone-800 font-medium">{{ $case->title }}</div>
                <div class="flex-1 text-sm text-stone-600">{{ $case->din_type }}</div>
                <div class="flex-1 text-sm text-stone-600">{{ $case->act }}</div>
                <div class="flex-1 text-sm text-stone-500 truncate">{{ $case->gemara_text }}</div>
                <div class="w-28 flex items-center gap-2">
                    @if (Auth::user() && Auth::user()->id == $case->user_id)
                        @if ($selectedId != $case->case_id)
                        <button type="button" :title="localizedTexts.remove"
                            wire:click="confirmRemove({{ $case->case_id }})"
                            class="p-1.5 text-stone-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            @include('icons.trash')
                        </button>
                        @else
                        <button type="button" :title="localizedTexts.remove"
                            wire:click="removeGemaraCase({{ $case->case_id }})"
                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                            @include('icons.check')
                        </button>
                        <button type="button" :title="localizedTexts.cancel"
                            wire:click="clearSelected()"
                            class="p-1.5 text-stone-400 hover:text-stone-600 hover:bg-stone-100 rounded-lg transition-colors">
                            @include('icons.ex')
                        </button>
                        @endif
                        <button type="button" :title="localizedTexts.edit"
                            x-on:click="editGemaraCase({{ $case->case_id }}, true)"
                            class="p-1.5 text-stone-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-colors">
                            @include('icons.edit')
                        </button>
                    @else
                    <button type="button" :title="localizedTexts.view"
                        x-on:click="editGemaraCase({{ $case->case_id }}, false)"
                        class="p-1.5 text-stone-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-colors">
                        @include('icons.view')
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

    </div>

    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 mb-12">
        {{ $gemaraCases->links() }}
    </div>
</div>
