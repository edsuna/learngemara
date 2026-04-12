<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TractatesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function get_tractate(): void
    {
        $this->seed();
        $response = $this->get('/api/tractates/Eiruvin');

        $response->assertStatus(200);
    }
}
