<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Tag;
use App\Models\Translation;
use App\Repositories\TranslationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TranslationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TranslationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TranslationRepository();
    }

    public function test_can_find_all_translations(): void
    {
        Translation::factory()->count(3)->create();

        $result = $this->repository->findAll([], 15);

        $this->assertCount(3, $result->items());
    }

    public function test_can_find_translation_by_id(): void
    {
        $translation = Translation::factory()->create();

        $result = $this->repository->findById($translation->id);

        $this->assertNotNull($result);
        $this->assertEquals($translation->id, $result->id);
    }

    public function test_can_create_translation(): void
    {
        $data = [
            'key' => 'test_key',
            'locale' => 'en',
            'content' => 'Test content',
        ];

        $translation = $this->repository->create($data);

        $this->assertInstanceOf(Translation::class, $translation);
        $this->assertEquals('test_key', $translation->key);
    }

    public function test_can_update_or_create_translation(): void
    {
        $attributes = ['key' => 'test', 'locale' => 'en'];
        $values = ['content' => 'Initial content'];

        $translation1 = $this->repository->updateOrCreate($attributes, $values);
        $this->assertEquals('Initial content', $translation1->content);

        $values = ['content' => 'Updated content'];
        $translation2 = $this->repository->updateOrCreate($attributes, $values);

        $this->assertEquals($translation1->id, $translation2->id);
        $this->assertEquals('Updated content', $translation2->content);
    }

    public function test_can_update_translation(): void
    {
        $translation = Translation::factory()->create(['content' => 'Original']);

        $result = $this->repository->update($translation, ['content' => 'Updated']);

        $this->assertTrue($result);
        $this->assertEquals('Updated', $translation->fresh()->content);
    }

    public function test_can_delete_translation(): void
    {
        $translation = Translation::factory()->create();

        $result = $this->repository->delete($translation);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    public function test_can_get_by_locale(): void
    {
        Translation::factory()->create([
            'key' => 'test1',
            'locale' => 'en',
            'content' => 'Content 1',
        ]);
        Translation::factory()->create([
            'key' => 'test2',
            'locale' => 'en',
            'content' => 'Content 2',
        ]);
        Translation::factory()->create([
            'key' => 'test3',
            'locale' => 'fr',
            'content' => 'Contenu 3',
        ]);

        $result = $this->repository->getByLocale('en');

        $this->assertCount(2, $result);
        $this->assertEquals('Content 1', $result['test1']);
    }

    public function test_can_get_all_grouped_by_locale(): void
    {
        Translation::factory()->create(['key' => 'test', 'locale' => 'en', 'content' => 'English']);
        Translation::factory()->create(['key' => 'test', 'locale' => 'fr', 'content' => 'French']);

        $result = $this->repository->getAllGroupedByLocale();

        $this->assertArrayHasKey('en', $result);
        $this->assertArrayHasKey('fr', $result);
        $this->assertEquals('English', $result['en']['test']);
    }

    public function test_can_sync_tags(): void
    {
        $translation = Translation::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $this->repository->syncTags($translation, [$tag1->id, $tag2->id]);

        $this->assertCount(2, $translation->tags);
    }

    public function test_filters_work_correctly(): void
    {
        Translation::factory()->create(['locale' => 'en', 'key' => 'test1']);
        Translation::factory()->create(['locale' => 'fr', 'key' => 'test2']);

        $result = $this->repository->findAll(['locale' => 'en'], 15);

        $this->assertCount(1, $result->items());
        $this->assertEquals('en', $result->items()[0]->locale);
    }
}
