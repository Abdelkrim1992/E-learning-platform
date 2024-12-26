<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;

class ExampleTest extends TestCase
{

    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/test'); 
    
        $response->assertStatus(500);
    }
}
