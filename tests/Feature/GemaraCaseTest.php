<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\GemaraCase;
use PHPUnit\Framework\Attributes\Test;

class GemaraCaseTest extends TestCase
{
    use RefreshDatabase;

    private $caseData = [
        'masechet' => 'Shabbat',
        'daf' => '7b',
        'gemaraText' => 'gemara text',
        'title' => 'title',
        'dinType' => 'מותר',
        'inputConditions' => [
            'consequences' => [
                'value' => '',
                'notRelevant' => true,
            ],
            'when' => [
                'value' => 'Tuesday',
                'notRelevant' => false,
            ],
            'where' => [
                'value' => 'Israel',
                'notRelevant' => false,
            ],
            'toWhat' => [
                'value' => 'an animal',
                'notRelevant' => true,
            ],
            'withWhat' => [
                'value' => 'a knife',
                'notRelevant' => false,
            ],
            'how' => [
                'value' => 'on purpose',
                'notRelevant' => false,
            ],
            'other' => [
                'value' => '',
                'notRelevant' => true,
            ],
            'who' => [
                'value' => 'anyone',
                'notRelevant' => true,
            ],
        ],
        'act' => 'damage',
        'public' => false,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function save_not_logged_in(): void
    {
        $response = $this->postJson('/gemara_cases');
        $response->assertStatus(401);
    }

    #[Test]
    public function create_not_logged_in(): void
    {
        $response = $this->get('/gemara_cases/create');
        $response->assertStatus(200);
        $response->assertSee('Analytic Skillset Tool');
    }

    #[Test]
    public function invalid_case(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/gemara_cases');
        $response->assertStatus(422);
    }

    #[Test]
    public function save_case(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/gemara_cases', $this->caseData);

        $response->assertStatus(201);
    }

    #[Test]
    public function missing_data(): void
    {
        $user = User::factory()->create();
        $data = $this->caseData;

        unset($data['act']);
        $response = $this->actingAs($user)->postJson('/gemara_cases', $data);
        $response->assertStatus(422);
    }

    #[Test]
    public function update_case_not_owner(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);
        $data = $this->caseData;
        $data['user_id'] = $user->id;
        $data['caseId'] = $case->id;

        $response = $this->actingAs($user2)->json('PUT', '/gemara_cases/' . $case->id, $data);
        $response->assertStatus(403);
    }

    #[Test]
    public function update_case(): void
    {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);
        $data = $this->caseData;
        $data['user_id'] = $user->id;
        $data['caseId'] = $case->id;

        $response = $this->actingAs($user)->json('PUT', '/gemara_cases/' . $case->id, $data);
        $response->assertStatus(200);
    }

    #[Test]
    public function get_my_gemara_cases(): void
    {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/gemara_cases/');
        $response->assertStatus(200)
            ->assertSee('case-row')
            ->assertSeeLivewire('gemara-cases');
    }

    #[Test]
    public function create_gemara_case_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/gemara_cases/create');
        $response->assertStatus(200)
            ->assertSee('localizedTexts.pageTitle');
    }

    #[Test]
    public function show_gemara_case(): void
    {
        $user = User::factory()->create();
        // Explicit title, for the same reason as modify_gemara_case.
        $case = GemaraCase::factory()->create(['user_id' => $user->id, 'title' => 'a plain title']);

        $response = $this->actingAs($user)->get('/gemara_cases/' . $case->id);
        $response->assertStatus(200)
            ->assertSee('localizedTexts.pageTitle')
            ->assertSee($case->title);
    }

    #[Test]
    public function modify_gemara_case(): void
    {
        $user = User::factory()->create();
        // An explicit title: the case reaches the page only through toJson() in
        // x-init, so a Faker title containing a quote or slash is JSON-escaped
        // differently from assertSee's HTML escaping and flakes intermittently.
        $case = GemaraCase::factory()->create(['user_id' => $user->id, 'title' => 'a plain title']);

        $response = $this->actingAs($user)->get('/gemara_cases/' . $case->id . '/edit');
        $response->assertStatus(200)
            ->assertSee('localizedTexts.pageTitle')
            ->assertSee($case->title);
    }

    #[Test]
    public function home_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200)
            ->assertSee('Analytic skillset tool');
    }

    #[Test]
    public function home_page_shows_my_cases_link_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200)
            ->assertSee('My Gemara Cases');
    }

    #[Test]
    public function get_public_gemara_cases(): void
    {
        $user = User::factory()->create();
        GemaraCase::factory()->create(['user_id' => $user->id, 'public' => 1]);

        $response = $this->get('/gemara_cases/?public=1');
        $response->assertStatus(200)
            ->assertSeeLivewire('gemara-cases');
    }

    #[Test]
    public function show_gemara_case_not_owner(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user2)->get('/gemara_cases/' . $case->id);
        $response->assertStatus(200);
    }

    #[Test]
    public function gemara_case_has_tractate_relationship(): void
    {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        $this->assertNotNull($case->tractate);
        $this->assertEquals($case->masechet, $case->tractate->english_name);
    }

    #[Test]
    public function gemara_case_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        $this->assertNotNull($case->user);
        $this->assertEquals($user->id, $case->user->id);
    }

    /**
     * COND-10 - Regression: a case with a Not Relevant condition must be
     * re-savable without touching it.
     *
     * Marking a condition Not Relevant stores it as NULL, and the `_nr` flag
     * comes back from MySQL as integer 1. `required_unless:{c}_nr,true` only
     * exempts a real boolean, so the reloaded 1 did not exempt it, the NULL
     * value failed `required`, and saving an untouched case returned 422.
     * Re-ticking the checkbox sent a real boolean and appeared to "fix" it.
     */
    #[Test]
    public function a_case_with_not_relevant_conditions_can_be_resaved_unchanged(): void
    {
        $user = User::factory()->create();

        $created = $this->actingAs($user)
            ->postJson('/gemara_cases', $this->caseData)
            ->assertStatus(201);

        $case = GemaraCase::findOrFail($created->json('id'));

        // 'other' was sent as Not Relevant; confirm how it actually landed.
        $this->assertNull($case->other, 'A Not Relevant condition stores NULL');
        $this->assertTrue($case->other_nr, 'The _nr flag must cast to a real boolean');

        // Rebuild the payload the way the edit form hydrates it: straight from
        // the model's JSON, with nothing edited.
        $payload = $this->caseData;
        $payload['caseId'] = $case->id;
        foreach (GemaraCase::inputConditions as $inputCondition) {
            $payload['inputConditions'][$inputCondition] = [
                'value' => $case->{$inputCondition},
                'notRelevant' => $case->{$inputCondition . '_nr'},
            ];
        }

        $this->actingAs($user)
            ->putJson('/gemara_cases/' . $case->id, $payload)
            ->assertStatus(200);
    }

    /**
     * COND-11 - The _nr flags are booleans in the JSON the edit form consumes.
     * If they serialise as 1/0 the round-trip above breaks again.
     */
    #[Test]
    public function not_relevant_flags_serialise_as_booleans(): void
    {
        $user = User::factory()->create();
        $id = $this->actingAs($user)
            ->postJson('/gemara_cases', $this->caseData)
            ->json('id');

        $json = GemaraCase::findOrFail($id)->toArray();

        foreach (GemaraCase::inputConditions as $inputCondition) {
            $this->assertIsBool(
                $json[$inputCondition . '_nr'],
                "{$inputCondition}_nr must serialise as a boolean, not an integer"
            );
        }
    }
}
