<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TranslationRepositoryInterface
{
    public function findAll(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(int $id): ?Translation;

    public function create(array $data): Translation;

    public function updateOrCreate(array $attributes, array $values): Translation;

    public function update(Translation $translation, array $data): bool;

    public function delete(Translation $translation): bool;

    public function getByLocale(string $locale, array $tags = []): array;

    public function getAllGroupedByLocale(array $tags = []): array;

    public function syncTags(Translation $translation, array $tagIds): void;
}
