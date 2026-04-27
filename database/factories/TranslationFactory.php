<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected static $keyPrefixes = [
        'button', 'label', 'message', 'error', 'success', 'warning', 
        'title', 'description', 'placeholder', 'tooltip', 'heading',
        'nav', 'menu', 'form', 'field', 'action', 'status', 'notification'
    ];

    protected static $locales = ['en', 'fr', 'es', 'de', 'it', 'pt', 'ja', 'zh', 'ar', 'ru'];

    public function definition(): array
    {
        $prefix = fake()->randomElement(self::$keyPrefixes);
        $suffix = fake()->word();
        
        return [
            'key' => $prefix . '_' . $suffix . '_' . fake()->unique()->numberBetween(1, 1000000),
            'locale' => fake()->randomElement(self::$locales),
            'content' => fake()->sentence(),
        ];
    }

    public function locale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }

    public function withKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
        ]);
    }
}
