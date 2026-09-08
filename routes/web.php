<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Home;
use App\Http\Controllers\GemaraCaseController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Passwords\Confirm;
use App\Livewire\Auth\Passwords\Email;
use App\Livewire\Auth\Passwords\Reset;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\Verify;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', Home::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
    Route::get('register', Register::class)->name('register');
});

Route::get('password/reset', Email::class)->name('password.request');
Route::get('password/reset/{token}', Reset::class)->name('password.reset');

Route::middleware('auth')->group(function () {
    Route::get('email/verify', Verify::class)
        ->middleware('throttle:6,1')
        ->name('verification.notice');

    Route::get('password/confirm', Confirm::class)->name('password.confirm');
});

Route::middleware('auth')->group(function () {
    Route::get('email/verify/{id}/{hash}', EmailVerificationController::class)
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('logout', LogoutController::class)->name('logout');
});

Route::resource('gemara_cases', GemaraCaseController::class, ['only' => ['create', 'index', 'show']]);
Route::resource('gemara_cases', GemaraCaseController::class, ['except' => ['create', 'index', 'show']])->middleware('auth');

Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/callback', function () {
    try {
        $user = Socialite::driver('google')->user();

        $finduser = User::where('google_id', $user->id)->first();

        if ($finduser) {
            Auth::login($finduser);
            return redirect('/');
        } else {
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'google_id' => $user->id,
                // No usable password: this account signs in through Google.
                // The model's 'hashed' cast bcrypts whatever is set here, and
                // the plaintext is random and discarded, so nothing can match
                // it. AUTH-37 covers telling such a user what to do instead.
                'password' => Str::random(48),
            ]);

            Auth::login($newUser);
            return redirect('/');
        }
    } catch (Throwable $e) {
        // Was dd($e->getMessage()), which dumped a debug page carrying the
        // exception text straight to the visitor. Report it and hand the user
        // back somewhere they can act.
        report($e);

        return redirect()->route('login')
            ->with('error', 'Google sign-in did not complete. Please try again, or sign in with your email and password.');
    }
});
