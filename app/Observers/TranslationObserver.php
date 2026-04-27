<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

final class TranslationObserver
{
    public function created(Translation $translation): void
    {
        $this->clearCache();
    }

    public function updated(Translation $translation): void
    {
        $this->clearCache();
    }

    public function deleted(Translation $translation): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::flush();
    }
}
