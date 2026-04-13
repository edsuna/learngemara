<div
    x-data="languageToggle()"
    x-init="setTitle('{{ $hebrewTitle }}', '{{ $englishTitle }}')"
    @togglelanguage.window="toggleLanguage()"
    class="-mx-8"
>
    <nav class="top-0 w-full min-w-full bg-[#faf6ed] border-b-2 border-[#c7b299] py-4 justify-between px-8"
        :class="(selectedLanguage === 'Hebrew') ? 'rtl left-0 ' : 'right-0 mr-4 '"
    >
        <div class="flex max-w-screen-xl mx-auto justify-between items-center">
            <div>
                <a href="{{ route('home') }}" class="font-[Frank_Ruhl_Libre] text-xl font-bold text-stone-900">
                    LearnGemara
                </a>
            </div>
            @if (Route::has('login'))
            <div class="flex items-center gap-4"
                :class="(selectedLanguage === 'Hebrew') ? 'ml-4 ' : 'mr-4 '"
            >
                @auth
                    <a
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="text-indigo-700 hover:text-indigo-900 underline decoration-indigo-300 underline-offset-4 transition ease-in-out duration-150"
                        x-text="localizedTexts.LogOut"
                    >
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="text-indigo-700 hover:text-indigo-900 underline decoration-indigo-300 underline-offset-4 transition ease-in-out duration-150"
                        x-text="localizedTexts.LogIn"
                    >
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-md shadow-sm transition ease-in-out duration-150"
                            x-text="localizedTexts.Register"
                        >
                        </a>
                    @endif
                @endauth
            </div>
            @endif
            <div
                :class="selectedLanguage === 'English' ? '' : 'rtl'"
                class="cursor-pointer bg-white hover:bg-yellow-50 rounded-md border border-[#c7b299] shadow-sm font-[Frank_Ruhl_Libre] py-2 px-4 h-12 flex items-center"
                @click="changeLanguage()"
                x-text="localizedTexts.buttonText"
            >
            </div>
        </div>
    </nav>
    <div class="flex items-center justify-center gap-4 mt-6 mb-2">
        <span class="h-px w-12 bg-[#c7b299]"></span>
        <h1 class="font-[Frank_Ruhl_Libre] text-[2.5rem] font-bold text-stone-900 text-center" x-text="localizedTexts.pageTitle"></h1>
        <span class="h-px w-12 bg-[#c7b299]"></span>
    </div>
</div>
