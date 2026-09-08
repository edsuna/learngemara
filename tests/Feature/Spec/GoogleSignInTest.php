<?php

namespace Tests\Feature\Spec;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * docs/spec/auth.md - Google sign-in
 *
 * The OAuth callback creates accounts and had no coverage of any kind, despite
 * being a path that writes to the users table.
 */
class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $id, string $name, string $email): void
    {
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = $id;
        $socialiteUser->name = $name;
        $socialiteUser->email = $email;

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    /** AUTH-34 - A known Google account signs in. */
    #[Test]
    public function a_known_google_account_signs_in(): void
    {
        $existing = User::factory()->create([
            'google_id' => 'google-abc',
            'email' => 'someone@example.com',
        ]);

        $this->fakeGoogleUser('google-abc', 'Someone', 'someone@example.com');

        $this->get('/auth/callback')->assertRedirect('/');

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::count(), 'no second account may be created');
    }

    /** AUTH-35 - An unknown Google account creates a user. */
    #[Test]
    public function an_unknown_google_account_creates_a_user(): void
    {
        $this->fakeGoogleUser('google-new', 'New Person', 'new@example.com');

        $this->get('/auth/callback')->assertRedirect('/');

        $created = User::where('google_id', 'google-new')->first();
        $this->assertNotNull($created);
        $this->assertSame('New Person', $created->name);
        $this->assertSame('new@example.com', $created->email);
        $this->assertAuthenticatedAs($created);
    }

    /**
     * AUTH-37 - A Google-registered user is told how to sign in.
     *
     * Such an account has no usable password: a random one is set and
     * discarded. The generic "these credentials do not match" is true but
     * useless -- no password would work, and nothing said so.
     */
    #[Test]
    public function a_google_account_is_told_to_use_google_rather_than_a_password(): void
    {
        $this->fakeGoogleUser('google-one', 'One', 'one@example.com');
        $this->get('/auth/callback');
        $user = User::where('google_id', 'google-one')->firstOrFail();

        // No guessable password, and not one shared between such accounts.
        $this->assertFalse(\Hash::check('123456dummy', $user->password));

        Auth::logout();

        Livewire::test('auth.login')
            ->set('email', 'one@example.com')
            ->set('password', 'anything at all')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString(
            'Google',
            trans('auth.google_account'),
            'the message must name the route that actually works'
        );
    }

    /** AUTH-37 - An ordinary account still gets the ordinary message. */
    #[Test]
    public function a_password_account_still_gets_the_generic_failure_message(): void
    {
        User::factory()->create(['email' => 'normal@example.com', 'google_id' => null]);

        Livewire::test('auth.login')
            ->set('email', 'normal@example.com')
            ->set('password', 'the wrong password')
            ->call('authenticate')
            ->assertHasErrors(['email' => trans('auth.failed')]);
    }

    /**
     * AUTH-36 - An OAuth failure is handled rather than dumped.
     *
     * The callback used to dd($e->getMessage()), putting a debug page carrying
     * the exception text in front of the visitor.
     */
    #[Test]
    public function a_failed_google_callback_returns_the_user_to_login(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andThrow(new \RuntimeException('invalid state'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();

        // The exception text must not be handed to the visitor.
        $this->assertStringNotContainsString('invalid state', (string) session('error'));
    }
}
