<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // The home page sends visitors to the sections list.
    public function test_home_redirects_to_sections(): void
    {
        $this->get('/')->assertRedirect('/sections');
    }
}
