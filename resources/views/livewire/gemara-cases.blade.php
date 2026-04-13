<div>
    <div
        x-data="gemaraCase()"
        x-init="getMasechtot('', false); $wire.set('masechet', ''); $wire.set('daf', ''); $wire.set('searchText', '')"
        @togglelanguage.window="toggleLanguage()"
        :class="{'rtl': selectedLanguage === 'Hebrew'} "
        class="max-w-screen-xl mx-auto"
    >
        <div wire:ignore class="flex mb-8">
            <div class="mx-4">
                <select
                    x-model="theCase.masechet"
                    @change="selectMasechet(); $nextTick(() => { $wire.set('masechet', $el.value) })">
                    <option value="" x-text="localizedTexts.selectMasechet"></option>
                    <template x-for="masechet in masechtot">
                        <option class="text-slate-800" :key="masechet.englishName" :value="masechet.englishName" x-text="(selectedLanguage === 'English') ? masechet.englishName.replace('_', ' ') : masechet.text">
                    </template>
                </select>
            </div>
            <div class="mx-4">
                <select x-model="theCase.daf"
                    @change="$nextTick(() => { $wire.set('daf', $el.value) })">
                    <template x-for="daf in dapim">
                        <option class="text-slate-800" :key="daf.value" :value="daf.value" x-text="daf.text">
                    </template>
                </select>
            </div>
            <div>
                <input type="text"
                    class="p-1 border-gray-500 border-2"
                    x-model="theCase.searchText"
                    @input.debounce.300ms="$wire.set('searchText', $el.value)"
                    :placeholder="localizedTexts.searchText">
            </div>
        </div>
        <div class="overflow-hidden rounded-xl shadow-md">
            <div class="flex justify-between bg-[#1e3a5f] px-4 py-3">
                <div class="flex-1 text-xs font-bold text-[#d4a843] uppercase tracking-wider" x-text="localizedTexts.masechetLabel">
                </div>
                <div class="flex-1 text-xs font-bold text-[#d4a843] uppercase tracking-wider" x-text="localizedTexts.dafLabel">
                </div>
                <div class="flex-1 text-xs font-bold text-[#d4a843] uppercase tracking-wider" x-text="localizedTexts.titleLabel">
                </div>
                <div class="flex-1 text-xs font-bold text-[#d4a843] uppercase tracking-wider" x-text="localizedTexts.caseDin">
                </div>
                <div class="flex-1 text-xs font-bold text-[#d4a843] uppercase tracking-wider" x-text="localizedTexts.caseAct">
                </div>
                <div class="flex-1 text-xs font-bold text-[#d4a843] uppercase tracking-wider" x-text="localizedTexts.text">
                </div>
                <div class="w-32 text-xs font-bold text-[#d4a843] uppercase tracking-wider" x-text="localizedTexts.actions">
                </div>
            </div>
            @foreach ($gemaraCases as $case)
            <div class="flex justify-between case-row px-4 py-3 odd:bg-slate-50 hover:bg-[#f0d78c]/20 transition-colors duration-150">
                <div class="flex-1 text-[#1e3a5f] font-semibold" x-show="selectedLanguage !== 'Hebrew'">
                    {{ str_replace('_', ' ', $case->masechet) }}
                </div>
                <div class="flex-1 text-[#1e3a5f] font-semibold" x-show="selectedLanguage === 'Hebrew'">
                    {{ $case->name }}
                </div>
                <div class="flex-1">
                    {{ $case->daf }}
                </div>
                <div class="flex-1 text-[#1e3a5f] font-semibold">
                    {{ $case->title }}
                </div>
                <div class="flex-1 mx-4">
                    {{ $case->din_type }}
                </div>
                <div class="flex-1">
                    {{ $case->act }}
                </div>
                <div class="flex-1">
                    {{ $case->gemara_text }}
                </div>
                <div class="w-32 mx-4">
                    @if (Auth::user() && Auth::user()->id == $case->user_id)
                        @if ($selectedId != $case->case_id)
                        <button type="button"
                            :title="localizedTexts.remove"
                            wire:click="confirmRemove({{ $case->case_id }})"
                            class="hover:text-[#d4a843] hover:bg-[#f0d78c]/30 rounded p-1 transition-colors duration-150">
                            @include('icons.trash')
                        </button>
                        @else
                        <button type="button"
                            :title="localizedTexts.remove"
                            wire:click="removeGemaraCase({{ $case->case_id }})"
                            class="hover:text-[#d4a843] hover:bg-[#f0d78c]/30 rounded p-1 transition-colors duration-150">
                            @include('icons.check')
                        </button>
                        <button type="button"
                            :title="localizedTexts.cancel"
                            wire:click="clearSelected()"
                            class="hover:text-[#d4a843] hover:bg-[#f0d78c]/30 rounded p-1 transition-colors duration-150">
                            @include('icons.ex')
                        </button>
                        @endif
                        <button type="button"
                            :title="localizedTexts.edit"
                            x-on:click="editGemaraCase({{ $case->case_id }}, true)"
                            class="hover:text-[#d4a843] hover:bg-[#f0d78c]/30 rounded p-1 transition-colors duration-150">
                            @include('icons.edit')
                        </button>
                    @else
                    <button type="button"
                        :title="localizedTexts.view"
                        x-on:click="editGemaraCase({{ $case->case_id }}, false)"
                        class="hover:text-[#d4a843] hover:bg-[#f0d78c]/30 rounded p-1 transition-colors duration-150">
                        @include('icons.view')
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

    </div>

    <div class="max-w-screen-xl mx-auto mt-6 mb-12">
        {{ $gemaraCases->links() }}
    </div>
</div>
