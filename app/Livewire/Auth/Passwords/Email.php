<?php

namespace App\Livewire\Auth\Passwords;

use Livewire\Component;
use Illuminate\Support\Facades\Password;

class Email extends Component
{
    public ?string $email = '';
    public string|false $emailSentMessage = false;

    public function sendResetPasswordLink()
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $response = $this->broker()->sendResetLink(['email' => $this->email]);

        if ($response == Password::RESET_LINK_SENT) {
            $this->emailSentMessage = trans($response);
            return;
        }

        $this->addError('email', trans($response));
    }

    public function broker()
    {
        return Password::broker();
    }

    public function render()
    {
        return view('livewire.auth.passwords.email')->layout('layouts.auth');
    }
}
