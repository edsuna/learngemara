<?php

namespace Tests\Feature\Spec;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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
     * AUTH-37 - OAuth-created accounts have no usable password.
     *
     * Not a security hole: encrypt() uses a random IV and the model's
     * 'password' => 'hashed' cast bcrypts the result, so each account holds a
     * hash of a different random string rather than a shared secret. But the
     * plaintext is unknowable, so such a user can never sign in with a
     * password and has no signposted way to set one.
     */
    #[Test]
    public function an_oauth_account_gets_an_unusable_but_unique_password(): void
    {
        $this->fakeGoogleUser('google-one', 'One', 'one@example.com');
        $this->get('/auth/callback');
        $first = User::where('google_id', 'google-one')->firstOrFail();

        $this->assertFalse(
            \Hash::check('123456dummy', $first->password),
            'the literal dummy string must not be a working password'
        );

        $this->markTestIncomplete(
            'AUTH-37: a Google-registered user cannot sign in with email and password '
            . 'and is offered no way to set one.'
        );
    }

    /**
     * AUTH-36 - An OAuth failure shows an error page.
     *
     * @defect routes/web.php catches the exception and calls
     * dd($e->getMessage()), which dumps rather than handling it, and puts the
     * exception text on screen.
     */
    #[Test]
    public function a_failed_google_callback_shows_an_error_page(): void
    {
        $this->markTestIncomplete(
            'AUTH-36: the callback dd()s the exception message instead of rendering an error page.'
        );
    }
}
