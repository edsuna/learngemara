<?php

namespace Tests\Feature\Spec;

use App\Models\GemaraCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * docs/spec/saving.md
 *
 * These assert what ends up in the database, not just the status code. The
 * existing suite only checked for 201/200, which means a change that silently
 * stopped persisting a field -- dropping it from $fillable, say -- would leave
 * every test green while losing data. That is the exact failure mode the
 * Stage 0d location migration could produce.
 */
class SavingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** A complete, valid payload in the shape the browser actually sends. */
    private function payload(array $overrides = []): array
    {
        $payload = [
            'masechet' => 'Shabbat',
            'daf' => '7b',
            'gemaraText' => 'the gemara text',
            'title' => 'a title',
            'dinType' => 'מותר',
            'act' => 'carrying',
            'public' => false,
            'inputConditions' => [
                'consequences' => ['value' => 'a consequence', 'notRelevant' => false],
                'when' => ['value' => 'on shabbat', 'notRelevant' => false],
                'where' => ['value' => 'a public domain', 'notRelevant' => false],
                'toWhat' => ['value' => 'an object', 'notRelevant' => false],
                'withWhat' => ['value' => 'a hand', 'notRelevant' => false],
                'how' => ['value' => 'deliberately', 'notRelevant' => false],
                'other' => ['value' => '', 'notRelevant' => true],
                'who' => ['value' => 'an adult', 'notRelevant' => false],
            ],
        ];

        return array_replace($payload, $overrides);
    }

    /** SAVE-02 - An unauthenticated POST is rejected. */
    #[Test]
    public function a_guest_cannot_create_a_case(): void
    {
        $this->postJson('/gemara_cases', $this->payload())->assertStatus(401);
        $this->assertDatabaseCount('gemara_cases', 0);
    }

    /**
     * SAVE-07 - Every field round-trips into the database.
     *
     * The point of this test is the assertions after the 201, not the 201.
     */
    #[Test]
    public function saving_persists_every_field(): void
    {
        $user = User::factory()->create();

        $id = $this->actingAs($user)
            ->postJson('/gemara_cases', $this->payload())
            ->assertStatus(201)
            ->json('id');

        $case = GemaraCase::findOrFail($id);

        $this->assertSame('Shabbat', $case->masechet);
        $this->assertSame('7b', $case->daf);
        $this->assertSame('the gemara text', $case->gemara_text, 'gemaraText must map to gemara_text');
        $this->assertSame('a title', $case->title);
        $this->assertSame('מותר', $case->din_type, 'dinType must map to din_type');
        $this->assertSame('carrying', $case->act);
        $this->assertFalse($case->public);
        $this->assertSame($user->id, $case->user_id, 'ownership is set server-side');

        // The nested {value, notRelevant} objects must flatten into columns.
        $this->assertSame('a consequence', $case->consequences);
        $this->assertSame('on shabbat', $case->when);
        $this->assertSame('a public domain', $case->where);
        $this->assertSame('an object', $case->toWhat);
        $this->assertSame('a hand', $case->withWhat);
        $this->assertSame('deliberately', $case->how);
        $this->assertSame('an adult', $case->who);

        foreach (['consequences', 'when', 'where', 'toWhat', 'withWhat', 'how', 'who'] as $answered) {
            $this->assertFalse($case->{$answered . '_nr'}, "{$answered} was answered, so its _nr flag must be false");
        }
        $this->assertTrue($case->other_nr, 'other was marked Not Relevant');
    }

    /** SAVE-08 - Editing an existing case updates it rather than duplicating. */
    #[Test]
    public function saving_an_existing_case_updates_it(): void
    {
        $user = User::factory()->create();
        $id = $this->actingAs($user)->postJson('/gemara_cases', $this->payload())->json('id');

        $edited = $this->payload(['act' => 'a different act', 'caseId' => $id]);

        $this->actingAs($user)->putJson('/gemara_cases/' . $id, $edited)->assertStatus(200);

        $this->assertDatabaseCount('gemara_cases', 1);
        $this->assertSame('a different act', GemaraCase::findOrFail($id)->act);
    }

    /** SAVE-10 - Save as New clones rather than updating. */
    #[Test]
    public function save_as_new_clones_the_case(): void
    {
        $user = User::factory()->create();
        $originalId = $this->actingAs($user)->postJson('/gemara_cases', $this->payload())->json('id');

        // "Save as New" zeroes caseId client-side so the request becomes a create.
        $clone = $this->payload(['act' => 'a cloned act', 'caseId' => 0]);

        $cloneId = $this->actingAs($user)->postJson('/gemara_cases', $clone)
            ->assertStatus(201)->json('id');

        $this->assertNotSame($originalId, $cloneId);
        $this->assertDatabaseCount('gemara_cases', 2);
        $this->assertSame('carrying', GemaraCase::findOrFail($originalId)->act, 'the original must be untouched');
        $this->assertSame('a cloned act', GemaraCase::findOrFail($cloneId)->act);
    }

    /** SAVE-11 - A missing required field is rejected. */
    #[Test]
    public function a_case_without_an_act_is_rejected(): void
    {
        $payload = $this->payload();
        unset($payload['act']);

        $this->actingAs(User::factory()->create())
            ->postJson('/gemara_cases', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('act');
    }

    /** SAVE-12 - An unknown masechet is rejected. */
    #[Test]
    public function an_unknown_masechet_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/gemara_cases', $this->payload(['masechet' => 'Not_A_Tractate']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('masechet');
    }

    /** SAVE-13 - An invalid din type is rejected. */
    #[Test]
    public function a_din_type_outside_the_six_adjudications_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/gemara_cases', $this->payload(['dinType' => 'maybe']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('din_type');
    }

    /** SAVE-14 - An unanswered, non-N/R condition is rejected. */
    #[Test]
    public function a_condition_that_is_neither_answered_nor_not_relevant_is_rejected(): void
    {
        $payload = $this->payload();
        $payload['inputConditions']['who'] = ['value' => '', 'notRelevant' => false];

        $this->actingAs(User::factory()->create())
            ->postJson('/gemara_cases', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('who');
    }

    /** SAVE-15 - An N/R condition with no value is accepted. */
    #[Test]
    public function a_not_relevant_condition_needs_no_value(): void
    {
        $payload = $this->payload();
        foreach (GemaraCase::inputConditions as $inputCondition) {
            $payload['inputConditions'][$inputCondition] = ['value' => '', 'notRelevant' => true];
        }

        $id = $this->actingAs(User::factory()->create())
            ->postJson('/gemara_cases', $payload)
            ->assertStatus(201)
            ->json('id');

        $case = GemaraCase::findOrFail($id);
        foreach (GemaraCase::inputConditions as $inputCondition) {
            $this->assertTrue($case->{$inputCondition . '_nr'});
        }
    }

    /**
     * SAVE-16 - A server error is reported usefully.
     *
     * @defect The only error surface is alert(result.message), so a 422 shows a
     * summary string with no indication of which field failed, and the .catch()
     * branch reports nothing at all.
     */
    #[Test]
    public function a_validation_failure_tells_the_user_which_field_failed(): void
    {
        $this->markTestIncomplete('SAVE-16: known defect, no per-field error surface in the UI.');
    }

    /**
     * SAVE-17 - Update targets the case named in the URL.
     *
     * @defect GemaraCaseController::update() ignores its route-model-bound
     * instance and re-finds the case by $request['caseId'], then returns the
     * bound model. authorize() checks caseId so it is not exploitable today,
     * but the row written and the row returned can differ.
     */
    #[Test]
    public function update_writes_to_the_case_in_the_url(): void
    {
        $this->markTestIncomplete('SAVE-17: known defect, update() keys off the request body rather than the URL.');
    }
}
