<?php

declare(strict_types=1);

namespace VmEngine\Example\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use VmEngine\Example\Factories\ExampleNodeFactory;
use VmEngine\Synapse\Traits\WithDeleteToken;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $description
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read ExampleNode|null $parent
 * @property-read Collection<int, ExampleNode> $children
 */
class ExampleNode extends Model
{
    /** @use HasFactory<ExampleNodeFactory> */
    use HasFactory;

    use WithDeleteToken;

    protected $fillable = ['parent_id', 'name', 'description', 'position'];

    protected static function newFactory(): ExampleNodeFactory
    {
        return ExampleNodeFactory::new();
    }

    /** @return BelongsTo<ExampleNode, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<ExampleNode, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    /**
     * True when $nodeId is this node or one of its ancestors (moving $nodeId here would create a cycle).
     */
    public function isWithinBranchOf(int $nodeId): bool
    {
        $current = $this;

        while ($current !== null) {
            if ($current->id === $nodeId) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }

    /** @param  Builder<self>  $builder */
    #[Scope]
    protected function roots(Builder $builder): void
    {
        $builder->whereNull('parent_id')->orderBy('position');
    }
}
