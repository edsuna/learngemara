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
     * VIEW-09 - An unlisted case is still readable by anyone with its URL.
     *
     * Deliberate: `public` governs listing, not access, so a case can be shared
     * as a link without being published to the public list. Confirmed as
     * intended behavior, so this asserts it rather than flagging it.
     */
    #[Test]
    public function an_unlisted_case_is_readable_by_anyone_with_its_url(): void
    {
        $case = GemaraCase::factory()->create(['public' => false, 'title' => 'an unlisted title']);

        $this->get('/gemara_cases/' . $case->id)
            ->assertOk()
            ->assertSee('an unlisted title');
    }
}
