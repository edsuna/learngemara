<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\GemaraCase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class GemaraCasesLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function can_render_gemara_cases_component(): void
    {
        $user = User::factory()->create();
        GemaraCase::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('gemara-cases', ['public' => false])
            ->assertStatus(200);
    }

    #[Test]
    public function can_render_public_cases(): void
    {
        $user = User::factory()->create();
        GemaraCase::factory()->create(['user_id' => $user->id, 'public' => 1]);

        Livewire::test('gemara-cases', ['public' => true])
            ->assertStatus(200);
    }

    #[Test]
    public function can_filter_by_masechet(): void
    {
        $user = User::factory()->create();
        GemaraCase::factory()->create(['user_id' => $user->id, 'masechet' => 'Shabbat', 'public' => 1]);
        GemaraCase::factory()->create(['user_id' => $user->id, 'masechet' => 'Berakhot', 'public' => 1]);

        Livewire::test('gemara-cases', ['public' => true])
            ->set('masechet', 'Shabbat')
            ->assertSee('Shabbat')
            ->assertDontSee('Berakhot');
    }

    #[Test]
    public function can_filter_by_daf(): void
    {
        $user = User::factory()->create();
        GemaraCase::factory()->create(['user_id' => $user->id, 'masechet' => 'Shabbat', 'daf' => '7a', 'public' => 1]);
        GemaraCase::factory()->create(['user_id' => $user->id, 'masechet' => 'Shabbat', 'daf' => '10b', 'public' => 1]);

        Livewire::test('gemara-cases', ['public' => true])
            ->set('daf', '7a')
            ->assertSee('7a')
            ->assertDontSee('10b');
    }

    #[Test]
    public function can_search_by_text(): void
    {
        $user = User::factory()->create();
        GemaraCase::factory()->create([
            'user_id' => $user->id,
            'gemara_text' => 'unique search term here',
            'public' => 1,
        ]);
        GemaraCase::factory()->create([
            'user_id' => $user->id,
            'gemara_text' => 'something else entirely',
            'public' => 1,
        ]);

        Livewire::test('gemara-cases', ['public' => true])
            ->set('searchText', 'unique search term')
            ->assertSee('unique search term here')
            ->assertDontSee('something else entirely');
    }

    #[Test]
    public function can_confirm_and_remove_case(): void
    {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('gemara-cases', ['public' => false])
            ->call('confirmRemove', $case->id)
            ->assertSet('selectedId', $case->id)
            ->call('removeGemaraCase', $case->id);

        $this->assertDatabaseMissing('gemara_cases', ['id' => $case->id]);
    }

    #[Test]
    public function can_clear_selected(): void
    {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('gemara-cases', ['public' => false])
            ->call('confirmRemove', $case->id)
            ->assertSet('selectedId', $case->id)
            ->call('clearSelected')
            ->assertSet('selectedId', 0);
    }

    #[Test]
    public function update_masechet_resets_page(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('gemara-cases', ['public' => false])
            ->call('updateMasechet')
            ->assertSet('selectedId', 0);
    }

    /**
     * AUTHZ-04 - A guest cannot delete a case.
     *
     * The public case list renders for unauthenticated visitors, and Livewire
     * actions are callable by anyone who can render the component. Hiding the
     * delete button in Blade is presentation, not authorization.
     */
    #[Test]
    public function a_guest_cannot_delete_a_case(): void
    {
        $owner = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $owner->id, 'public' => true]);

        Livewire::test('gemara-cases', ['public' => true])
            ->call('removeGemaraCase', $case->id)
            ->assertForbidden();

        $this->assertDatabaseHas('gemara_cases', ['id' => $case->id]);
    }

    /** AUTHZ-05 - A logged-in user cannot delete someone else's case. */
    #[Test]
    public function a_user_cannot_delete_another_users_case(): void
    {
        $owner = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $owner->id, 'public' => true]);
        $other = User::factory()->create();

        Livewire::actingAs($other)
            ->test('gemara-cases', ['public' => true])
            ->call('removeGemaraCase', $case->id)
            ->assertForbidden();

        $this->assertDatabaseHas('gemara_cases', ['id' => $case->id]);
    }

    /** AUTHZ-06 - The owner can still delete their own case. */
    #[Test]
    public function the_owner_can_delete_their_own_case(): void
    {
        $owner = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $owner->id]);

        Livewire::actingAs($owner)
            ->test('gemara-cases', ['public' => false])
            ->call('removeGemaraCase', $case->id);

        $this->assertDatabaseMissing('gemara_cases', ['id' => $case->id]);
    }

    /** AUTHZ-07 - Deleting a case that does not exist is forbidden, not a 500. */
    #[Test]
    public function deleting_a_missing_case_is_forbidden(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('gemara-cases', ['public' => false])
            ->call('removeGemaraCase', 999999)
            ->assertForbidden();
    }
}
