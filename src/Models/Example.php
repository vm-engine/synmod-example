<?php

namespace VmEngine\Example\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use VmEngine\Example\Factories\ExampleFactory;
use VmEngine\Synapse\Traits\WithDeleteToken;

/**
 * @property int $id
 * @property int $category_id
 * @property string $text
 * @property string $email
 * @property string $protected
 * @property int $number
 * @property int $masked
 * @property string $textarea
 * @property int $dropdown
 * @property array $multidropdown
 * @property int $radio
 * @property array $checkbox
 * @property string $date
 * @property Carbon $datetime
 * @property string $file
 * @property string $color
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read ExampleCategory $category
 */
class Example extends Model
{
    use HasFactory;
    use WithDeleteToken;

    protected $fillable = [
        'category_id',
        'text',
        'textarea',
        'email',
        'protected',
        'number',
        'masked',
        'dropdown',
        'multidropdown',
        'radio',
        'checkbox',
        'date',
        'datetime',
        'file',
        'color',
    ];

    protected $casts = [
        'multidropdown' => 'array',
        'checkbox' => 'array',
        'datetime' => 'datetime',
    ];

    public static $options = [
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
    ];

    protected static function newFactory()
    {
        return ExampleFactory::new();
    }

    #[Scope]
    protected function search(Builder $builder, string $q): void
    {
        $builder->whereAny(['text', 'textarea', 'email'], 'like', "%{$q}%");
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExampleCategory::class, 'category_id');
    }
}
