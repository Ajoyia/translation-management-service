<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class TranslationTest extends TestCase
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

    public function test_can_create_translation(): void
    {
        $response = $this->postJson('/api/translations', [
            'key' => 'welcome_message',
            'locale' => 'en',
            'content' => 'Welcome to our application',
            'tags' => ['web', 'mobile'],
        ], $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'key', 'locale', 'content', 'tags'],
            ]);

        $this->assertDatabaseHas('translations', [
            'key' => 'welcome_message',
            'locale' => 'en',
            'content' => 'Welcome to our application',
        ]);
    }

    public function test_can_list_translations(): void
    {
        Translation::factory()->count(5)->create();

        $response = $this->getJson('/api/translations', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'key', 'locale', 'content'],
                ],
            ]);
    }

    public function test_can_filter_translations_by_locale(): void
    {
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'fr']);

        $response = $this->getJson('/api/translations?locale=en', $this->authHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $translation) {
            $this->assertEquals('en', $translation['locale']);
        }
    }

    public function test_can_search_translations_by_content(): void
    {
        Translation::factory()->create([
            'key' => 'test_key',
            'content' => 'searchable content',
        ]);
        Translation::factory()->create([
            'key' => 'other_key',
            'content' => 'different content',
        ]);

        $response = $this->getJson('/api/translations?search=searchable', $this->authHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(1, $data);
    }

    public function test_can_filter_translations_by_tags(): void
    {
        $webTag = Tag::factory()->create(['name' => 'web', 'slug' => 'web']);
        $mobileTag = Tag::factory()->create(['name' => 'mobile', 'slug' => 'mobile']);

        $translation1 = Translation::factory()->create();
        $translation1->tags()->attach($webTag);

        $translation2 = Translation::factory()->create();
        $translation2->tags()->attach($mobileTag);

        $response = $this->getJson('/api/translations?tags=web', $this->authHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(1, $data);
    }

    public function test_can_show_single_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->getJson("/api/translations/{$translation->id}", $this->authHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $translation->id,
                    'key' => $translation->key,
                    'locale' => $translation->locale,
                ],
            ]);
    }

    public function test_can_update_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->putJson("/api/translations/{$translation->id}", [
            'content' => 'Updated content',
        ], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Translation updated successfully',
            ]);

        $this->assertDatabaseHas('translations', [
            'id' => $translation->id,
            'content' => 'Updated content',
        ]);
    }

    public function test_can_delete_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->deleteJson("/api/translations/{$translation->id}", [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Translation deleted successfully',
            ]);

        $this->assertDatabaseMissing('translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_export_translations_by_locale(): void
    {
        Translation::factory()->create([
            'key' => 'welcome',
            'locale' => 'en',
            'content' => 'Welcome',
        ]);

        $response = $this->getJson('/api/translations/export?locale=en', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'locale',
                'translations',
                'generated_at',
            ]);

        $this->assertEquals('Welcome', $response->json('translations.welcome'));
    }

    public function test_can_export_all_translations(): void
    {
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'fr']);

        $response = $this->getJson('/api/translations/export/all', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'translations' => ['en', 'fr'],
                'generated_at',
            ]);
    }

    public function test_export_uses_cache(): void
    {
        Cache::flush();

        Translation::factory()->create([
            'key' => 'test',
            'locale' => 'en',
            'content' => 'Test',
        ]);

        $this->getJson('/api/translations/export?locale=en', $this->authHeaders());

        $cacheKey = 'translations.en.no-tags';
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_cache_clears_on_translation_update(): void
    {
        Cache::flush();

        $translation = Translation::factory()->create(['locale' => 'en']);
        
        $this->getJson('/api/translations/export?locale=en', $this->authHeaders());
        $this->assertTrue(Cache::store('array')->has('translations.en.no-tags') || Cache::has('translations.en.no-tags'));

        $translation->update(['content' => 'Updated']);

        $this->assertFalse(Cache::store('array')->has('translations.en.no-tags'));
    }

    public function test_can_manually_clear_cache(): void
    {
        Cache::put('test-key', 'test-value', 60);

        $response = $this->postJson('/api/translations/cache/clear', [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cache cleared successfully',
            ]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson('/api/translations');

        $response->assertStatus(401);
    }

    public function test_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->postJson('/api/translations', [
            'key' => 'test',
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['locale', 'content']);
    }

    public function test_pagination_works_correctly(): void
    {
        Translation::factory()->count(20)->create();

        $response = $this->getJson('/api/translations?per_page=5', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);
    }

    public function test_performance_handles_large_dataset(): void
    {
        Translation::factory()->count(1000)->create(['locale' => 'en']);

        $startTime = microtime(true);

        $response = $this->getJson('/api/translations/export?locale=en', $this->authHeaders());

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(500, $executionTime, 'Export should complete in less than 500ms');
    }
}
