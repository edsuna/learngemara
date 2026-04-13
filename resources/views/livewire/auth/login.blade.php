@section('title', 'Sign in to your account')

<div>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="{{ route('home') }}">
            <x-logo class="w-auto h-16 mx-auto text-teal-600" />
        </a>

        <h2 class="mt-6 text-3xl font-bold text-center text-stone-800 leading-9">
            Sign in to your account
        </h2>
        <p class="mt-2 text-sm text-center text-stone-600 leading-5 max-w">
            Or
            <a href="{{ route('register') }}" class="font-medium text-teal-600 hover:text-teal-700 focus:outline-none focus:underline transition ease-in-out duration-150">
                create a new account
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="px-4 py-8 bg-white shadow-sm border border-stone-200 sm:rounded-2xl sm:px-10">
            <form wire:submit="authenticate">
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 leading-5">
                        Email address
                    </label>

                    <div class="mt-1">
                        <input wire:model.blur="email" id="email" name="email" type="email" required autofocus class="appearance-none block w-full px-3 py-2.5 bg-stone-50 border border-stone-300 rounded-lg placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('email') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red-500 @enderror" />
                    </div>

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label for="password" class="block text-sm font-medium text-stone-700 leading-5">
                        Password
                    </label>

                    <div class="mt-1">
                        <input wire:model.blur="password" id="password" type="password" required class="appearance-none block w-full px-3 py-2.5 bg-stone-50 border border-stone-300 rounded-lg placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('password') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red-500 @enderror" />
                    </div>

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mt-6">
                    <div class="flex items-center">
                        <input wire:model.blur="remember" id="remember" type="checkbox" class="w-4 h-4 text-teal-600 transition duration-150 ease-in-out border-stone-300 rounded" />
                        <label for="remember" class="block ml-2 text-sm text-stone-700 leading-5">
                            Remember
                        </label>
                    </div>

                    <div class="text-sm leading-5">
                        <a href="{{ route('password.request') }}" class="font-medium text-teal-600 hover:text-teal-700 focus:outline-none focus:underline transition ease-in-out duration-150">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="flex justify-center w-full px-4 py-2.5 text-sm font-semibold text-white bg-teal-600 border border-transparent rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-150 ease-in-out">
                        Sign in
                    </button>
                </div>
            </form>
            <div class="text-center mt-6 text-sm text-stone-500">
                Or sign in with
            </div>
            <div class="mt-3">
                <a href="{{ url('auth/google') }}" class="block">
                    <button class="flex justify-center w-full px-4 py-2.5 text-sm font-medium text-stone-700 bg-white border-2 border-stone-300 rounded-lg hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-150 ease-in-out">
                        Google
                    </button>
                </a>
            </div>
        </div>
    </div>
</div>
