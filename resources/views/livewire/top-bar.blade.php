<div
    x-data="languageToggle()"
    x-init="setTitle('{{ $hebrewTitle }}', '{{ $englishTitle }}')"
    @togglelanguage.window="toggleLanguage()"
    class="-mx-8"
>
    <div class="top-0 w-full min-w-full bg-white py-4 justify-between px-8"
        :class="(selectedLanguage === 'Hebrew') ? 'rtl left-0 ' : 'right-0 mr-4 '"
    >
        <div class="flex max-w-screen-xl mx-auto justify-between">
            <div class="">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('storage/images/logo.png') }}">
                </a>
            </div>
            @if (Route::has('login'))
            <div class=""
                :class="(selectedLanguage === 'Hebrew') ? 'ml-4 ' : 'mr-4 '"
            >
                @auth
                    <a
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150"
                        x-text="localizedTexts.LogOut"
                    >
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150 mr-12"
                        x-text="localizedTexts.LogIn"
                    >
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150"
                            x-text="localizedTexts.Register"
                        >
                        </a>
                    @endif
                @endauth
            </div>
            @endif
            <div
                :class="selectedLanguage === 'English' ? '' : 'rtl'"
                class="cursor-pointer rounded-lg border-gray-400 border-2 py-2 px-4 h-12"
                @click="changeLanguage()"
                x-text="localizedTexts.buttonText"
            >
            </div>
        </div>
    </div>
    <h1 class="text-center text-4xl" x-text="localizedTexts.pageTitle"></h1>
</div>
