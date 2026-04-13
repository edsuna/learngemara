<div
    x-data="languageToggle()"
    x-init="setTitle('{{ $hebrewTitle }}', '{{ $englishTitle }}')"
    @togglelanguage.window="toggleLanguage()"
    class="-mx-8"
>
    <nav class="top-0 w-full min-w-full bg-[#1e3a5f] shadow-lg py-4 justify-between px-8"
        :class="(selectedLanguage === 'Hebrew') ? 'rtl left-0 ' : 'right-0 mr-4 '"
    >
        <div class="flex max-w-screen-xl mx-auto justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/logo.png') }}" class="brightness-0 invert">
                </a>
                <a href="{{ route('home') }}" class="text-white text-lg font-bold hidden sm:inline">LearnGemara</a>
            </div>
            @if (Route::has('login'))
            <div class="flex items-center gap-4"
                :class="(selectedLanguage === 'Hebrew') ? 'ml-4 ' : 'mr-4 '"
            >
                @auth
                    <a
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="text-white/80 hover:text-white text-sm font-semibold focus:outline-none transition ease-in-out duration-150"
                        x-text="localizedTexts.LogOut"
                    >
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="text-white/80 hover:text-white text-sm font-semibold focus:outline-none transition ease-in-out duration-150"
                        x-text="localizedTexts.LogIn"
                    >
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="text-[#1e3a5f] bg-[#d4a843] hover:bg-[#b8912e] px-5 py-2 rounded-full uppercase tracking-wide font-bold shadow-md transition ease-in-out duration-150"
                            x-text="localizedTexts.Register"
                        >
                        </a>
                    @endif
                @endauth
            </div>
            @endif
            <div
                :class="selectedLanguage === 'English' ? '' : 'rtl'"
                class="cursor-pointer bg-white/15 hover:bg-white/25 rounded-full border border-white/30 text-white text-sm font-semibold py-2 px-4 h-12 flex items-center transition ease-in-out duration-150"
                @click="changeLanguage()"
                x-text="localizedTexts.buttonText"
            >
            </div>
        </div>
    </nav>
    <div class="text-center py-6">
        <h1 class="text-[2.5rem] font-extrabold text-[#1e3a5f]" x-text="localizedTexts.pageTitle"></h1>
        <div class="mt-3 mx-auto w-24 h-1 bg-[#d4a843] rounded-full"></div>
    </div>
</div>
