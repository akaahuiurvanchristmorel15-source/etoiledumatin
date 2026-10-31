<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_send_a_policy_whose_nonce_matches_the_inline_scripts(): void
    {
        $this->seed();

        $response = $this->get(route('catalog.index'))->assertOk();
        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'self'", $policy);
        $this->assertStringContainsString("base-uri 'self'", $policy);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([A-Za-z0-9+\/=]+)'/", $policy);

        preg_match("/'nonce-([^']+)'/", $policy, $nonce);
        $this->assertStringContainsString('<script nonce="'.$nonce[1].'"', $response->getContent());
    }

    public function test_each_request_gets_a_new_nonce(): void
    {
        $first = $this->get(route('legal.notice'))->headers->get('Content-Security-Policy');
        $second = $this->get(route('legal.notice'))->headers->get('Content-Security-Policy');

        $this->assertNotSame($first, $second);
    }

    public function test_non_html_responses_have_no_policy(): void
    {
        $this->get(route('seo.robots'))->assertOk()->assertHeaderMissing('Content-Security-Policy');
    }

    /**
     * Garde-fou : la CSP bloque les attributs onclick/onchange/onerror… et les scripts inline sans nonce.
     */
    public function test_views_have_no_inline_handlers_or_scripts_without_nonce(): void
    {
        $offending = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            $content = $file->getContents();

            if (preg_match('/\son[a-z]+="/', $content)) {
                $offending[] = $file->getRelativePathname().' : attribut on…=""';
            }

            if (preg_match('/<script(?![^>]*\b(?:src|nonce)=)(?![^>]*application\/ld\+json)[^>]*>/', $content)) {
                $offending[] = $file->getRelativePathname().' : <script> inline sans nonce';
            }
        }

        $this->assertSame([], $offending, "Bloqué par la CSP :\n".implode("\n", $offending));
    }
}
