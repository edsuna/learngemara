<div class="absolute top-0 mt-4 flex"
    :class="(selectedLanguage === 'Hebrew') ? 'rtl left-0 ml-4 ' : 'right-0 mr-4 '"
    @togglelanguage.window="toggleLanguage()"
    x-data="languageToggle()"
>
    @if (Route::has('login'))
    <div class="space-x-4"
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
                class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150"
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
        :class="selectedLanguage === 'English' ? 'w-30' : 'rtl w-20'"
        class="cursor-pointer rounded-lg border-gray-400 border-2 p-2"
        @click="changeLanguage()"
        x-text="localizedTexts.buttonText"
    >
    </div>
</div>
