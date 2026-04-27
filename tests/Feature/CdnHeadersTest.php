<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CdnHeadersTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->token = $user->createToken('test-token')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_export_endpoints_have_cdn_headers(): void
    {
        $response = $this->getJson('/api/translations/export?locale=en', $this->authHeaders());

        $response->assertOk();
        
        $response->assertHeader('Cache-Control', 'public, max-age=3600, s-maxage=86400');
        $response->assertHeader('Vary', 'Accept-Encoding');
        $response->assertHeader('X-Cache-Status', 'MISS');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_export_all_endpoint_has_cdn_headers(): void
    {
        $response = $this->getJson('/api/translations/export/all', $this->authHeaders());

        $response->assertOk();
        
        $response->assertHeader('Cache-Control', 'public, max-age=3600, s-maxage=86400');
        $response->assertHeader('Vary', 'Accept-Encoding');
        $response->assertHeader('X-Cache-Status', 'MISS');
    }

    public function test_cdn_url_header_present_when_configured(): void
    {
        config(['app.cdn_url' => 'https://cdn.example.com']);

        $response = $this->getJson('/api/translations/export?locale=en', $this->authHeaders());

        $response->assertOk();
        $response->assertHeader('X-CDN-URL', 'https://cdn.example.com');
    }

    public function test_non_export_endpoints_dont_have_cdn_headers(): void
    {
        $response = $this->getJson('/api/translations', $this->authHeaders());

        $response->assertOk();
        
        $this->assertFalse($response->headers->has('X-Cache-Status'));
    }
}
