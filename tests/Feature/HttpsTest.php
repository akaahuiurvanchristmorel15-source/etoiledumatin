<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HttpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_plain_http_pages_are_redirected_to_https_when_enforced(): void
    {
        config(['app.force_https' => true]);

        $this->get('http://localhost/mentions-legales?onglet=1')
            ->assertStatus(301)
            ->assertRedirect('https://localhost/mentions-legales?onglet=1');
    }

    public function test_plain_http_forms_keep_their_method_when_redirected(): void
    {
        config(['app.force_https' => true]);

        $this->post('http://localhost/connexion', ['email' => 'lecteur@gmail.com'])
            ->assertStatus(308)
            ->assertRedirect('https://localhost/connexion');
    }

    public function test_https_pages_are_served_with_hsts(): void
    {
        config(['app.force_https' => true]);

        $this->get('https://localhost/mentions-legales')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_plain_http_is_served_when_not_enforced(): void
    {
        config(['app.force_https' => false]);

        $this->get('http://localhost/mentions-legales')->assertOk();
    }
}
