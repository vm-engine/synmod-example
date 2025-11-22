<?php

namespace VmEngine\Example\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use VmEngine\Example\Factories\ExampleCategoryFactory;
use VmEngine\Synapse\Traits\WithDeleteToken;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ExampleCategory extends Model
{
    use HasFactory;
    use WithDeleteToken {
        WithDeleteToken::booted as deleteBooted;
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function newFactory()
    {
        return ExampleCategoryFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::deleteBooted();
    }

    #[Scope]
    protected function search(Builder $builder, string $q): void
    {
        $builder->whereAny(['name', 'description'], 'like', "%{$q}%");
    }

    #[Scope]
    protected function active(Builder $builder): void
    {
        $builder->where('is_active', true);
    }

    public function examples(): HasMany
    {
        return $this->hasMany(Example::class, 'category_id');
    }
}
