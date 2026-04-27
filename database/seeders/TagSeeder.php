<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'mobile', 'slug' => 'mobile'],
            ['name' => 'desktop', 'slug' => 'desktop'],
            ['name' => 'web', 'slug' => 'web'],
            ['name' => 'admin', 'slug' => 'admin'],
            ['name' => 'public', 'slug' => 'public'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['slug' => $tag['slug']], $tag);
        }
    }
}
