<?php

namespace Tests\Feature\Spec;

use App\Models\GemaraCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * docs/spec/viewing.md
 *
 * Only the server-rendered half of read-only mode is testable here. Most of
 * what `allowUpdates=false` suppresses is hidden by Alpine's x-show at runtime,
 * so the markup is present in the response either way -- those scenarios
 * (VIEW-01, 02, 03, 07, 08) belong in the browser suite.
 */
class ViewingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** VIEW-04 - A guest viewing a case is invited to log in. */
    #[Test]
    public function a_guest_viewing_a_case_is_invited_to_log_in(): void
    {
        $case = GemaraCase::factory()->create(['public' => true]);

        $this->get('/gemara_cases/' . $case->id)
            ->assertOk()
            ->assertSee('localizedTexts.needLogin', false)
            ->assertDontSee('localizedTexts.saveCase', false);
    }

    /** VIEW-05 - The owner gets the editable form. */
    #[Test]
    public function the_owner_gets_the_save_controls(): void
    {
        $owner = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->get('/gemara_cases/' . $case->id . '/edit')
            ->assertOk()
            ->assertSee('localizedTexts.saveCase', false);
    }

    /** VIEW-06 - Editing requires authentication. */
    #[Test]
    public function a_guest_cannot_open_the_edit_form(): void
    {
        $case = GemaraCase::factory()->create(['public' => true]);

        $this->get('/gemara_cases/' . $case->id . '/edit')
            ->assertRedirect('/login');
    }

    /**
     * VIEW-09 - A private case is not readable by strangers.
     *
     * @defect `public` gates listing, not access: show() has no authorization,
     * so a case marked private is served in full to any guest who requests its
     * URL, and ids are sequential. Left unfixed deliberately -- enforcing
     * ownership here would break unlisted links that may already be shared,
     * which is a product decision rather than a code one.
     */
    #[Test]
    public function a_private_case_is_not_served_to_strangers(): void
    {
        $case = GemaraCase::factory()->create(['public' => false, 'title' => 'a private title']);

        // Current behavior, recorded so the test documents what happens today:
        $this->get('/gemara_cases/' . $case->id)->assertOk()->assertSee('a private title');

        $this->markTestIncomplete(
            'VIEW-09: private cases are readable by URL. Awaiting a product decision '
            . 'on whether to enforce ownership, which would break shared unlisted links.'
        );
    }
}
