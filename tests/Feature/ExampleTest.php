<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_halaman_awal_mengarah_ke_splash_screen(): void
    {
        $this->get('/')->assertRedirect(route('splash'));
        $this->get(route('splash'))->assertOk();
    }
}
