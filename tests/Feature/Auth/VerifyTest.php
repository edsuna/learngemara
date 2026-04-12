<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;

class VerifyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_view_verification_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Auth::login($user);

        $this->get(route('verification.notice'))
            ->assertSuccessful()
            ->assertSeeLivewire('auth.verify');
    }

    #[Test]
    public function can_resend_verification_email(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user);

        Livewire::test('auth.verify')
            ->call('resend')
            ->assertDispatched('resent');
    }

    #[Test]
    public function can_verify(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Auth::login($user);

        $url = URL::temporarySignedRoute('verification.verify', Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)
            ->assertRedirect(route('home'));

        $this->assertTrue($user->hasVerifiedEmail());
    }

    #[Test]
    public function verify_with_wrong_id_throws_authorization_exception(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Auth::login($user);

        $url = URL::temporarySignedRoute('verification.verify', Carbon::now()->addMinutes(60), [
            'id' => $user->getKey() + 999,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)->assertForbidden();
    }

    #[Test]
    public function verify_with_wrong_hash_throws_authorization_exception(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Auth::login($user);

        $url = URL::temporarySignedRoute('verification.verify', Carbon::now()->addMinutes(60), [
            'id' => $user->getKey(),
            'hash' => 'wrong-hash',
        ]);

        $this->get($url)->assertForbidden();
    }

    #[Test]
    public function verify_already_verified_user_redirects_home(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        $url = URL::temporarySignedRoute('verification.verify', Carbon::now()->addMinutes(60), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)->assertRedirect(route('home'));
    }
}
