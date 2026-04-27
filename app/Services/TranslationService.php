<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tag;
use App\Models\Translation;
use App\Repositories\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

final class TranslationService
{
    public function __construct(
        private readonly TranslationRepositoryInterface $repository
    ) {
    }

    public function getAll(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->findAll($filters, $perPage);
    }

    public function getById(int $id): ?Translation
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Translation
    {
        $translation = $this->repository->updateOrCreate(
            [
                'key' => $data['key'],
                'locale' => $data['locale'],
            ],
            [
                'content' => $data['content'],
            ]
        );

        if (isset($data['tags']) && is_array($data['tags'])) {
            $this->assignTags($translation, $data['tags']);
        }

        return $translation->load('tags');
    }

    public function update(Translation $translation, array $data): Translation
    {
        $this->repository->update($translation, $data);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $this->assignTags($translation, $data['tags']);
        }

        return $translation->load('tags');
    }

    public function delete(Translation $translation): bool
    {
        return $this->repository->delete($translation);
    }

    public function exportByLocale(string $locale, array $tags = []): array
    {
        $cacheKey = $this->generateCacheKey($locale, $tags);

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($locale, $tags) {
            return $this->repository->getByLocale($locale, $tags);
        });
    }

    public function exportAll(array $tags = []): array
    {
        $cacheKey = $this->generateCacheKey('all', $tags);

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($tags) {
            return $this->repository->getAllGroupedByLocale($tags);
        });
    }

    public function clearCache(): void
    {
        Cache::flush();
    }

    private function assignTags(Translation $translation, array $tagNames): void
    {
        $tagIds = [];
        
        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        $this->repository->syncTags($translation, $tagIds);
    }

    private function generateCacheKey(string $locale, array $tags): string
    {
        if (empty($tags)) {
            $tagsString = 'no-tags';
        } else {
            sort($tags);
            $tagsString = implode('-', $tags);
        }
        
        return "translations.{$locale}.{$tagsString}";
    }
}
