<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('translations:populate {count=100000} {--chunk=1000}')]
#[Description('Populate translations table with test data for performance testing')]
class PopulateTranslations extends Command
{
    public function handle()
    {
        $count = (int) $this->argument('count');
        $chunkSize = (int) $this->option('chunk');

        $this->info("Populating {$count} translations in chunks of {$chunkSize}...");

        $locales = ['en', 'fr', 'es', 'de', 'it', 'pt', 'ja', 'zh', 'ar', 'ru'];
        $tags = \App\Models\Tag::pluck('id')->toArray();
        
        if (empty($tags)) {
            $this->error('No tags found. Please run seeders first.');
            return 1;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $keyPrefixes = [
            'button', 'label', 'message', 'error', 'success', 'warning',
            'title', 'description', 'placeholder', 'tooltip', 'heading',
            'nav', 'menu', 'form', 'field', 'action', 'status', 'notification'
        ];

        $keySuffixes = [
            'login', 'logout', 'submit', 'cancel', 'save', 'delete', 'edit',
            'create', 'update', 'view', 'search', 'filter', 'sort', 'export',
            'import', 'download', 'upload', 'confirm', 'reset', 'back'
        ];

        \DB::transaction(function () use ($count, $chunkSize, $locales, $tags, $bar, $keyPrefixes, $keySuffixes) {
            $chunks = ceil($count / $chunkSize);
            
            for ($i = 0; $i < $chunks; $i++) {
                $batchSize = min($chunkSize, $count - ($i * $chunkSize));
                $translations = [];
                $pivotData = [];

                for ($j = 0; $j < $batchSize; $j++) {
                    $prefix = $keyPrefixes[array_rand($keyPrefixes)];
                    $suffix = $keySuffixes[array_rand($keySuffixes)];
                    $uniqueId = ($i * $chunkSize) + $j + 1;
                    
                    $translation = [
                        'key' => "{$prefix}_{$suffix}_{$uniqueId}",
                        'locale' => $locales[array_rand($locales)],
                        'content' => $this->generateContent(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $translations[] = $translation;
                    $bar->advance();
                }

                \App\Models\Translation::insert($translations);

                $insertedIds = \App\Models\Translation::latest('id')
                    ->take($batchSize)
                    ->pluck('id')
                    ->toArray();

                foreach ($insertedIds as $translationId) {
                    $numTags = rand(1, 3);
                    $selectedTags = array_rand(array_flip($tags), $numTags);
                    $selectedTags = is_array($selectedTags) ? $selectedTags : [$selectedTags];
                    
                    foreach ($selectedTags as $tagId) {
                        $pivotData[] = [
                            'translation_id' => $translationId,
                            'tag_id' => $tagId,
                        ];
                    }
                }

                if (!empty($pivotData)) {
                    \DB::table('tag_translation')->insert($pivotData);
                }
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Successfully populated {$count} translations!");
        
        return 0;
    }

    private function generateContent(): string
    {
        $templates = [
            'Welcome to %s',
            'Click here to %s',
            'Please enter your %s',
            'Your %s has been saved',
            'Error: %s not found',
            'Successfully updated %s',
            '%s is required',
            'Are you sure you want to %s?',
        ];

        $words = ['account', 'profile', 'settings', 'dashboard', 'information', 'data', 'item'];
        
        return sprintf($templates[array_rand($templates)], $words[array_rand($words)]);
    }
}
