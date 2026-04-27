<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class TranslationRepository implements TranslationRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Translation::with('tags');

        if (isset($filters['locale'])) {
            $query->byLocale($filters['locale']);
        }

        if (isset($filters['key'])) {
            $query->byKey($filters['key']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['tags'])) {
            $query->byTags($filters['tags']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Translation
    {
        return Translation::with('tags')->find($id);
    }

    public function create(array $data): Translation
    {
        return Translation::create($data);
    }

    public function updateOrCreate(array $attributes, array $values): Translation
    {
        return Translation::updateOrCreate($attributes, $values);
    }

    public function update(Translation $translation, array $data): bool
    {
        return $translation->update($data);
    }

    public function delete(Translation $translation): bool
    {
        $translation->tags()->detach();
        
        return $translation->delete();
    }

    public function getByLocale(string $locale, array $tags = []): array
    {
        $query = Translation::select('key', 'content')
            ->byLocale($locale);

        if (!empty($tags)) {
            $query->byTags($tags);
        }

        return $query->pluck('content', 'key')->toArray();
    }

    public function getAllGroupedByLocale(array $tags = []): array
    {
        $query = Translation::select('key', 'locale', 'content');

        if (!empty($tags)) {
            $query->byTags($tags);
        }

        $translations = $query->get(['key', 'locale', 'content']);

        $result = [];
        foreach ($translations as $translation) {
            if (!isset($result[$translation->locale])) {
                $result[$translation->locale] = [];
            }
            $result[$translation->locale][$translation->key] = $translation->content;
        }

        return $result;
    }

    public function syncTags(Translation $translation, array $tagIds): void
    {
        $translation->tags()->sync($tagIds);
    }
}
