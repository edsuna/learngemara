<div>
    <div
        x-data="gemaraCaseList()"
        @togglelanguage.window="toggleLanguage()"
        :class="{'rtl': selectedLanguage === 'Hebrew'} "
        class="max-w-screen-xl mx-auto"
    >
        <div class="flex justify-between">
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.masechet">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.daf">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.title">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.dinType">
            </div>
            <div class="flex-1 font-bold border-b-2" x-text="localizedTexts.act">
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
                <button type="button"
                    x-on:click="editGemaraCase({{ $case->case_id }})">
                    @include('icons.edit')
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>
