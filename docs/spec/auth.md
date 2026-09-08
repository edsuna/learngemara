# Authentication

Email/password authentication through Livewire full-page components, plus Google OAuth via
Socialite. This is the one area of the application with thorough existing test coverage — 41
PHPUnit tests across login, registration, logout, email verification and password reset — so this
file records the behavior for completeness rather than to drive new test writing.

## Shape

Auth pages route **directly to Livewire class names** rather than to controllers:

```php
Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
    Route::get('register', Register::class)->name('register');
});
```

Each component validates with `$this->validate([...])`, reports failures with
`addError()`, and on success redirects with `redirect()->intended(route('home'))`. They select
their own layout in `render()` via `->layout('layouts.auth')`.

The `guest` middleware means a logged-in user visiting `/login` or `/register` is redirected away
rather than shown the form.

## Google OAuth

`/auth/google` redirects to Google; `/auth/callback` looks the user up by `google_id` and logs them
in, or creates a new user with the Google profile's name and email. Two things to know about the
created account: the password is a fixed dummy string (`encrypt('123456dummy')`), and the callback
is wrapped in a `try/catch` ending in `dd($e->getMessage())` — an OAuth failure dumps to the
browser rather than showing an error page.

---

## Scenarios

```gherkin
Feature: Login

  Scenario: AUTH-01 - The login page renders                                  (code)
  Scenario: AUTH-02 - Valid credentials sign the user in                      (observed)
  Scenario: AUTH-03 - A signed-in user is redirected home after login         (code)
  Scenario: AUTH-04 - An already signed-in user is redirected away from /login (code)
  Scenario: AUTH-05 - Email is required                                       (code)
  Scenario: AUTH-06 - Email must be well-formed                               (code)
  Scenario: AUTH-07 - Password is required                                    (code)
  Scenario: AUTH-08 - Bad credentials produce an error, not a sign-in         (code)
```

```gherkin
Feature: Registration

  Scenario: AUTH-09 - The registration page renders                           (code)
  Scenario: AUTH-10 - A valid registration creates the account and signs in   (code)
  Scenario: AUTH-11 - Name, email and password are each required              (code)
  Scenario: AUTH-12 - Email must be well-formed                               (code)
  Scenario: AUTH-13 - Email must be unique                                    (code)
  Scenario: AUTH-14 - Uniqueness is reported as the user types                (code)
  Scenario: AUTH-15 - Password must be at least 8 characters                  (code)
  Scenario: AUTH-16 - Password confirmation must match                        (code)
```

```gherkin
Feature: Logout

  Scenario: AUTH-17 - A signed-in user can sign out                           (code)
  Scenario: AUTH-18 - A guest cannot sign out                                 (code)
```

```gherkin
Feature: Email verification

  Scenario: AUTH-19 - The verification notice renders                         (code)
  Scenario: AUTH-20 - The verification email can be resent                    (code)
  Scenario: AUTH-21 - A correctly signed link verifies the address            (code)
  Scenario: AUTH-22 - A link with the wrong id is refused                     (code)
  Scenario: AUTH-23 - A link with the wrong hash is refused                   (code)
  Scenario: AUTH-24 - An already-verified user is redirected home             (code)
```

```gherkin
Feature: Password reset

  Scenario: AUTH-25 - The reset request page renders                          (code)
  Scenario: AUTH-26 - A reset request sends a notification                    (code)
  Scenario: AUTH-27 - Email is required and must be well-formed               (code)
  Scenario: AUTH-28 - A valid token resets the password                       (code)
  Scenario: AUTH-29 - Token, email and password are each required             (code)
  Scenario: AUTH-30 - The new password must meet the length rule              (code)
  Scenario: AUTH-31 - The new password confirmation must match                (code)
  Scenario: AUTH-32 - Password confirmation gates sensitive pages             (code)
  Scenario: AUTH-33 - A wrong password does not pass confirmation             (code)
```

```gherkin
Feature: Google sign-in

  Scenario: AUTH-34 - A known Google account signs in                         (code)
    Given a user exists with that google_id
    When they complete the Google callback
    Then they are signed in as that user

  Scenario: AUTH-35 - An unknown Google account creates a user                (code)
    Given no user exists with that google_id
    When they complete the Google callback
    Then a user is created with the Google name and email
    And they are signed in
```

## Known defects

```gherkin
Scenario: AUTH-36 - An OAuth failure is handled, not dumped               (code)
  # The callback used to dd($e->getMessage()), putting a debug page carrying
  # the exception text in front of the visitor. It now reports the exception
  # and returns the user to the login page with a message.
  Given the Google callback fails
  When I am returned to the application
  Then I am on the login page with an explanation
  And the exception text is not shown to me

Scenario: AUTH-37 - A Google-registered user is told how to sign in       (code)
  # Such an account has no usable password -- a random one is set and
  # discarded -- so the generic "these credentials do not match" is true but
  # useless: no password would work, and nothing said so. Login now recognises
  # a google_id on the account and names the route that does work.
  Given I registered through Google
  When I try to sign in with email and password
  Then I am told to use Google, or to reset my password to set one
  And an ordinary account still gets the generic failure message
```
