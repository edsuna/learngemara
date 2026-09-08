<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function authenticate()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $this->remember)) {
            $this->addError('email', $this->failureMessage($credentials['email']));

            return;
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Accounts created through Google have no usable password -- a random one
     * is set and discarded -- so "these credentials do not match" is true but
     * useless: there is no password that would work, and nothing tells the
     * user that. Point them at a route that does.
     */
    private function failureMessage(string $email): string
    {
        $user = User::where('email', $email)->first();

        if ($user && $user->google_id) {
            return trans('auth.google_account');
        }

        return trans('auth.failed');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.auth');
    }
}
