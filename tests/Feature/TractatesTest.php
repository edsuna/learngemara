<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TractatesTest extends TestCase
{
    /**
     * Get a single tractate.
     *
     * @return void
     */
    public function testGetTractate()
    {
        $this->seed();
        $response = $this->get('/api/tractates/Eiruvin');

        $response->assertStatus(200);
    }
}
