<?php

namespace Tests\Feature\Spec;

use App\Models\Tractate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * docs/spec/reference-data.md
 *
 * The list endpoint is what every masechet dropdown in the application is built
 * from, and it had no test at all.
 */
class ReferenceDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** REF-01 - The tractate list is publicly readable. */
    #[Test]
    public function the_tractate_list_is_public_and_complete(): void
    {
        $response = $this->getJson('/api/tractates')->assertOk();

        $tractates = $response->json();
        $this->assertIsArray($tractates);
        $this->assertCount(Tractate::count(), $tractates);

        // The client remaps these four keys; renaming any of them breaks every
        // masechet and daf dropdown in the application.
        foreach (['english_name', 'name', 'pages', 'has_last_amud'] as $key) {
            $this->assertArrayHasKey($key, $tractates[0], "the client depends on '{$key}'");
        }
    }

    /** REF-02 - A single tractate can be fetched by english_name. */
    #[Test]
    public function a_tractate_can_be_fetched_by_english_name(): void
    {
        $this->getJson('/api/tractates/Eiruvin')
            ->assertOk()
            ->assertJsonFragment(['english_name' => 'Eiruvin']);
    }

    /** REF-03 - An unknown tractate is not found. */
    #[Test]
    public function an_unknown_tractate_returns_not_found(): void
    {
        $this->getJson('/api/tractates/Not_A_Tractate')->assertNotFound();
    }
}
