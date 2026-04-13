@section('title', 'Sign in to your account')

<div>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="{{ route('home') }}">
            <x-logo class="w-auto h-16 mx-auto text-[#1e3a5f]" />
        </a>

        <h2 class="mt-6 text-3xl font-extrabold text-center text-[#1e3a5f] leading-9">
            Sign in to your account
        </h2>
        <p class="mt-2 text-sm text-center text-gray-600 leading-5 max-w">
            Or
            <a href="{{ route('register') }}" class="font-semibold text-[#d4a843] hover:text-[#b8912e] focus:outline-none focus:underline transition ease-in-out duration-150">
                create a new account
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="px-4 py-8 bg-white shadow-lg rounded-2xl border-2 border-slate-200 sm:px-10">
            <form wire:submit="authenticate">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
                        Email address
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.blur="email" id="email" name="email" type="email" required autofocus class="appearance-none block w-full px-3 py-2 border-2 border-slate-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-[#d4a843] focus:border-[#d4a843] transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('email') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red-500 @enderror" />
                    </div>

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 leading-5">
                        Password
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.blur="password" id="password" type="password" required class="appearance-none block w-full px-3 py-2 border-2 border-slate-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-[#d4a843] focus:border-[#d4a843] transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('password') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red-500 @enderror" />
                    </div>

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mt-6">
                    <div class="flex items-center">
                        <input wire:model.blur="remember" id="remember" type="checkbox" class="w-4 h-4 text-[#1e3a5f] transition duration-150 ease-in-out border-gray-300 rounded" />
                        <label for="remember" class="block ml-2 text-sm text-gray-900 leading-5">
                            Remember
                        </label>
                    </div>

                    <div class="text-sm leading-5">
                        <a href="{{ route('password.request') }}" class="font-semibold text-[#d4a843] hover:text-[#b8912e] focus:outline-none focus:underline transition ease-in-out duration-150">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div class="mt-6">
                    <span class="block w-full rounded-md shadow-sm">
                        <button type="submit" class="flex justify-center w-full px-4 py-3 font-bold text-white bg-[#1e3a5f] border border-transparent rounded-xl hover:bg-[#2a4a73] focus:outline-none focus:border-[#1e3a5f] focus:ring-[#d4a843] active:bg-[#162d4a] uppercase tracking-wide shadow-md transition duration-150 ease-in-out">
                            Sign in
                        </button>
                    </span>
                </div>
            </form>
            <div class="text-center mt-6">
                Or sign in with
            </div>
            <div>
                <a href="{{ url('auth/google') }}" class="mt-6 block w-full rounded-md shadow-sm">
                    <button class="flex justify-center w-full px-4 py-3 font-bold text-[#1e3a5f] bg-[#d4a843] border border-transparent rounded-xl hover:bg-[#b8912e] focus:outline-none focus:ring-[#d4a843] uppercase tracking-wide shadow-md transition duration-150 ease-in-out">
                        Google
                    </button>
                </a>
            </div>
        </div>
    </div>
</div>
