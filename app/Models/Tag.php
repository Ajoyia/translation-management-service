<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

final class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function translations(): BelongsToMany
    {
        return $this->belongsToMany(Translation::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $tag): void {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function (self $tag): void {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
