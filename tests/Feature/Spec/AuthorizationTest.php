<?php

namespace Tests\Feature\Spec;

use App\Models\GemaraCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * docs/spec/authorization.md
 *
 * AUTHZ-04 through AUTHZ-07 (the delete rules) live in
 * tests/Feature/GemaraCasesLivewireTest.php, alongside the component they
 * guard.
 */
class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** AUTHZ-01 - Writes require authentication. */
    #[Test]
    public function writes_require_authentication(): void
    {
        $this->postJson('/gemara_cases', [])->assertStatus(401);
    }

    /** AUTHZ-02 - Editing requires authentication. */
    #[Test]
    public function the_edit_form_requires_authentication(): void
    {
        $case = GemaraCase::factory()->create();

        $this->get('/gemara_cases/' . $case->id . '/edit')->assertRedirect('/login');
    }

    /** AUTHZ-03 - A user cannot update someone else's case. */
    #[Test]
    public function a_user_cannot_update_another_users_case(): void
    {
        $owner = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $owner->id, 'act' => 'the original act']);
        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->putJson('/gemara_cases/' . $case->id, ['caseId' => $case->id, 'act' => 'a hijacked act'])
            ->assertStatus(403);

        $this->assertSame('the original act', $case->fresh()->act);
    }

    /** AUTHZ-08 - Reading a public case does not require authentication. */
    #[Test]
    public function reading_a_public_case_does_not_require_authentication(): void
    {
        $case = GemaraCase::factory()->create(['public' => true]);

        $this->get('/gemara_cases/' . $case->id)->assertOk();
    }
}
