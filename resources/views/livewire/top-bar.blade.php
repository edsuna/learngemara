<div
    x-data="languageToggle()"
    x-init="setTitle('{{ $hebrewTitle }}', '{{ $englishTitle }}')"
    @togglelanguage.window="toggleLanguage()"
>
    <nav class="sticky top-0 z-40 w-full bg-white/80 backdrop-blur-md border-b border-stone-200"
        :class="(selectedLanguage === 'Hebrew') ? 'rtl' : ''"
    >
        <div class="flex items-center justify-between max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" class="h-8 w-auto transition-transform duration-200 group-hover:scale-105">
                <span class="hidden sm:inline text-lg font-semibold text-stone-800">LearnGemara</span>
            </a>

            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a
                            href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="text-sm font-medium text-stone-600 hover:text-teal-600 transition-colors duration-200"
                            x-text="localizedTexts.LogOut"
                        ></a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-stone-600 hover:text-teal-600 transition-colors duration-200"
                            x-text="localizedTexts.LogIn"
                        ></a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 px-4 py-2 rounded-lg transition-colors duration-200"
                                x-text="localizedTexts.Register"
                            ></a>
                        @endif
                    @endauth
                @endif

                <button
                    @click="changeLanguage()"
                    class="px-3 py-1.5 text-sm font-medium text-stone-600 bg-stone-100 hover:bg-stone-200 rounded-full transition-colors duration-200 border border-stone-200"
                    x-text="localizedTexts.buttonText"
                ></button>
            </div>
        </div>
    </nav>

    <h1 class="text-center text-4xl font-bold text-stone-800 mt-10 mb-6"
        x-text="localizedTexts.pageTitle"></h1>
</div>
