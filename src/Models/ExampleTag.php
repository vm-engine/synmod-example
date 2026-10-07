<?php

declare(strict_types=1);

namespace VmEngine\Example\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use VmEngine\Example\Factories\ExampleTagFactory;
use VmEngine\Synapse\Traits\WithDeleteToken;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $color
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ExampleTag extends Model
{
    /** @use HasFactory<ExampleTagFactory> */
    use HasFactory;

    use WithDeleteToken;

    protected $fillable = ['name', 'slug', 'color'];

    protected static function newFactory(): ExampleTagFactory
    {
        return ExampleTagFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (ExampleTag $tag): void {
            if (empty($tag->slug)) {
                $tag->slug = static::uniqueSlug($tag->name);
            }
        });
    }

    /**
     * Slug from $name, suffixed (-2, -3, ...) until unique.
     */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'tag';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /** @return BelongsToMany<Example, $this> */
    public function examples(): BelongsToMany
    {
        return $this->belongsToMany(Example::class, 'example_example_tag');
    }
}
