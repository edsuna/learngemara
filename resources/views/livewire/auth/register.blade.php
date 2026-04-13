@section('title', 'Create a new account')

<div>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="{{ route('home') }}">
            <x-logo class="w-auto h-16 mx-auto text-[#1e3a5f]" />
        </a>

        <h2 class="mt-6 text-3xl font-extrabold text-center text-[#1e3a5f] leading-9">
            Create a new account
        </h2>

        <p class="mt-2 text-sm text-center text-gray-600 leading-5 max-w">
            Or
            <a href="{{ route('login') }}" class="font-semibold text-[#d4a843] hover:text-[#b8912e] focus:outline-none focus:underline transition ease-in-out duration-150">
                sign in to your account
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="px-4 py-8 bg-white shadow-lg rounded-2xl border-2 border-slate-200 sm:px-10">
            <form wire:submit="register">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 leading-5">
                        Name
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.blur="name" id="name" type="text" required autofocus class="appearance-none block w-full px-3 py-2 border-2 border-slate-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-[#d4a843] focus:border-[#d4a843] transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('name') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red-500 @enderror" />
                    </div>

                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
                        Email address
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.blur="email" id="email" type="email" required class="appearance-none block w-full px-3 py-2 border-2 border-slate-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-[#d4a843] focus:border-[#d4a843] transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('email') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red-500 @enderror" />
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

                <div class="mt-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 leading-5">
                        Confirm Password
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.blur="passwordConfirmation" id="password_confirmation" type="password" required class="block w-full px-3 py-2 placeholder-gray-400 border-2 border-slate-300 appearance-none rounded-xl focus:outline-none focus:ring-[#d4a843] focus:border-[#d4a843] transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                    </div>
                </div>

                <div class="mt-6">
                    <span class="block w-full rounded-md shadow-sm">
                        <button type="submit" class="flex justify-center w-full px-4 py-3 font-bold text-white bg-[#1e3a5f] border border-transparent rounded-xl hover:bg-[#2a4a73] focus:outline-none focus:border-[#1e3a5f] focus:ring-[#d4a843] active:bg-[#162d4a] uppercase tracking-wide shadow-md transition duration-150 ease-in-out">
                            Register
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
