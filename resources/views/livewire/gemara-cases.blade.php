<div>
    <div
        x-data="languageToggle()"
        @togglelanguage.window="toggleLanguage()"
        :class="{'rtl': selectedLanguage === 'Hebrew'} "
    >
        @foreach ($gemaraCases as $case)
        <div class="flex justify-between">
            <div class="flex-1" x-show="selectedLanguage !== 'Hebrew'">
                {{ $case->masechet }}
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
            <div>
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
            </div>
        </div>
        @endforeach
    </div>
</div>
