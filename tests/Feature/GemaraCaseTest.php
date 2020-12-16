<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class GemaraCaseTest extends TestCase
{
    use RefreshDatabase;

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
        $data = [
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

        $response = $this->actingAs($user)->postJson('/gemara_cases', $data);

        $response->assertStatus(201);
    }
}
