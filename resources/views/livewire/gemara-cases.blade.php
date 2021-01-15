<div>
    <div
        x-data="gemaraCase()"
        x-init="getMasechtot('', false)"
        @togglelanguage.window="toggleLanguage()"
        :class="{'rtl': selectedLanguage === 'Hebrew'} "
        class="max-w-screen-xl mx-auto"
    >
        <div class="flex justify-between mb-8">
            <div>
                <select wire:model="masechet"
                    @change='selectMasechet()'>
                    <option value="" x-text="localizedTexts.selectMasechet"></option>
                    <template x-for="masechet in masechtot">
                        <option :key="masechet.englishName" :value="masechet.englishName" x-text="(selectedLanguage === 'English') ? masechet.englishName.replace('_', ' ') : masechet.text">
                    </template>
                </select>
            </div>
            <div>
                <select wire:modecl='daf'>
                    <template x-for="daf in dapim">
                        <option :key="daf.value" :value="daf.value" x-text="daf.text">
                    </template>
                </select>
            </div>
        </div>
        <div class="flex justify-between">
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.masechetLabel">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.dafLabel">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.titleLabel">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.caseDin">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.caseAct">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.text">
            </div>
            <div class="font-bold border-b-2 w-32" x-text="localizedTexts.actions">
            </div>
        </div>
        @foreach ($gemaraCases as $case)
        <div class="flex justify-between case-row">
            <div class="flex-1" x-show="selectedLanguage !== 'Hebrew'">
                {{ str_replace('_', ' ', $case->masechet) }}
            </div>
            <div class="flex-1" x-show="selectedLanguage === 'Hebrew'">
                {{ $case->name }}
            </div>
            <div class="flex-1">
                {{ $case->daf }}
            </div>
            <div class="flex-1">
                {{ $case->title }}
            </div>
            <div class="flex-1">
                {{ $case->din_type }}
            </div>
            <div class="flex-1">
                {{ $case->act }}
            </div>
            <div class="flex-1">
                {{ $case->gemara_text }}
            </div>
            <div class="w-32">
                @if ($selectedId != $case->case_id)
                <button type="button"
                    wire:click="confirmRemove({{ $case->case_id }})">
                    @include('icons.trash')
                </button>
                @else
                <button type="button"
                    wire:click="removeGemaraCase({{ $case->case_id }})">
                    @include('icons.check')
                </button>
                <button type="button"
                    wire:click="clearSelected()">
                    @include('icons.ex')
                </button>
                @endif
                @if (Auth::user() && Auth::user()->id == $case->user_id)
                <button type="button"
                    x-on:click="editGemaraCase({{ $case->case_id }}, true)">
                    @include('icons.edit')
                </button>
                @else
                <button type="button"
                    x-on:click="editGemaraCase({{ $case->case_id }}, false)">
                    @include('icons.view')
                </button>
                @endif
            </div>
        </div>
        @endforeach

        {{ $gemaraCases->links() }}
    </div>
</div>
