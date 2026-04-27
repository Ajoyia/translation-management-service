<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Translation;
use App\Observers\TranslationObserver;
use App\Repositories\TranslationRepository;
use App\Repositories\TranslationRepositoryInterface;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TranslationRepositoryInterface::class,
            TranslationRepository::class
        );
    }

    public function boot(): void
    {
        Translation::observe(TranslationObserver::class);
    }
}
