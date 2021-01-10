<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\GemaraCase;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

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

    protected function setUp(): void {
        parent::setUp();

        $this->seed();
    }

    public function testSaveNotLoggedIn() {
        $response = $this->postJson('/gemara_cases');
        $response->assertStatus(401);
    }

    public function testCreateNotLoggedIn() {
        $response = $this->get('/gemara_cases/create');
        $response->assertStatus(200);
        $response->assertSee('Analytic Skillset Tool');
    }

    public function testInvalidCase() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/gemara_cases');
        $response->assertStatus(422);
    }

    /**
     * @group test
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testSaveCase() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/gemara_cases', $this->caseData);

        $response->assertStatus(201);
    }

    public function testMissingData() {
        $user = User::factory()->create();
        $data = $this->caseData;

        unset($data['act']);
        $response = $this->actingAs($user)->postJson('/gemara_cases', $data);
        $response->assertStatus(422);
    }

    public function testUpdateCaseNotOwner() {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);
        $data = $this->caseData;
        $data['user_id'] = $user->id;
        $data['caseId'] = $case->id;

        $response = $this->actingAs($user2)->json('PUT', '/gemara_cases/' . $case->id, $data);
        $response->assertStatus(403);
    }

    public function testUpdateCase() {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);
        $data = $this->caseData;
        $data['user_id'] = $user->id;
        $data['caseId'] = $case->id;

        $response = $this->actingAs($user)->json('PUT', '/gemara_cases/' . $case->id, $data);
        $response->assertStatus(200);
    }

    public function testGetMyGemaraCases() {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/gemara_cases/');
        $response->assertStatus(200)
            ->assertSee('case-row')
            ->assertSeeLivewire('top-bar');
    }

    public function testCreateGemaraCaseForm() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/gemara_cases/create');
        $response->assertStatus(200)
            ->assertSee('localizedTexts.pageTitle');
    }

    public function testShowGemaraCase() {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/gemara_cases/' . $case->id);
        $response->assertStatus(200)
            ->assertSee('localizedTexts.pageTitle')
            ->assertSee($case->title);

    }

    public function testModifyGemaraCase() {
        $user = User::factory()->create();
        $case = GemaraCase::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/gemara_cases/' . $case->id . '/edit');
        $response->assertStatus(200)
            ->assertSee('localizedTexts.pageTitle')
            ->assertSee($case->title);

    }
}
