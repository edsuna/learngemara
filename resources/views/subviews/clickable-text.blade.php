<span x-show="{{ $show }}"
    @dblclick="{{ $dblClick }}"
    class="border-b-4 border-dashed mr-4 bg-white"
    :class="(selectedLanguage === 'Hebrew') ? 'rtl ml-4 ' : 'mr-4 '"
    x-text="{{ $text }}"></span>
