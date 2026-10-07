<?php

declare(strict_types=1);

namespace VmEngine\Example\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Factories\ExampleFactory;
use VmEngine\Example\Observers\ExampleObserver;
use VmEngine\Synapse\Traits\WithDeleteToken;
use VmEngine\SynAuth\Traits\HasCreator;
use VmEngine\SynAuth\Traits\HasUpdater;

/**
 * @property int $id
 * @property int|null $category_id
 * @property string $text
 * @property string $slug
 * @property ExampleStatus $status
 * @property Carbon|null $due_at
 * @property string $email
 * @property string $protected
 * @property int $number
 * @property int $masked
 * @property string $textarea
 * @property string|null $content
 * @property array<string, mixed>|null $meta
 * @property int $position
 * @property int $dropdown
 * @property array<int, int|string> $multidropdown
 * @property int $radio
 * @property array<int, int|string> $checkbox
 * @property string $date
 * @property Carbon $datetime
 * @property string $file
 * @property string $color
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read ExampleCategory|null $category
 */
#[ObservedBy([ExampleObserver::class])]
class Example extends Model
{
    use HasCreator;

    /** @use HasFactory<ExampleFactory> */
    use HasFactory;

    use HasUpdater;
    use SoftDeletes;
    use WithDeleteToken;

    protected $fillable = [
        'category_id',
        'text',
        'slug',
        'status',
        'due_at',
        'textarea',
        'content',
        'meta',
        'position',
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

    /**
     * Legacy showcase columns are NOT NULL; default them so the newer forms
     * (editor, drawer, wizards) can create rows without those fields.
     */
    protected $attributes = [
        'status' => 'draft',
        'protected' => '',
        'number' => 0,
        'dropdown' => 0,
    ];

    protected $casts = [
        'status' => ExampleStatus::class,
        'due_at' => 'date',
        'meta' => 'array',
        'multidropdown' => 'array',
        'checkbox' => 'array',
        'datetime' => 'datetime',
    ];

    /** @var list<int> */
    public static array $options = [
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
    ];

    protected static function newFactory(): ExampleFactory
    {
        return ExampleFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (Example $example): void {
            if (empty($example->status)) {
                $example->status = ExampleStatus::Draft;
            }

            if (empty($example->slug)) {
                $example->slug = static::uniqueSlug((string) $example->text);
            }
        });

        // Attachment rows cascade at the DB level, which skips model events —
        // delete them through the model first so their files go too.
        static::forceDeleting(function (Example $example): void {
            $example->attachments()->get()->each->delete();
        });

        // Soft-deleted examples can be restored, so their upload stays until a force delete.
        static::forceDeleted(function (Example $example): void {
            $disk = Storage::disk('public');

            if ($example->file && $disk->exists($example->file)) {
                $disk->delete($example->file);
            }
        });
    }

    /**
     * Slug from $text, suffixed (-2, -3, ...) until unique across all rows, trashed included.
     */
    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::slug($text) ?: 'example';
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /** @param  Builder<self>  $builder */
    #[Scope]
    protected function search(Builder $builder, string $q): void
    {
        $builder->whereAny(['text', 'textarea', 'email'], 'like', "%{$q}%");
    }

    /** @param  Builder<self>  $builder */
    #[Scope]
    protected function published(Builder $builder): void
    {
        $builder->where('status', ExampleStatus::Published->value);
    }

    /** @return BelongsTo<ExampleCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExampleCategory::class, 'category_id');
    }

    /** @return BelongsToMany<ExampleTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ExampleTag::class, 'example_example_tag');
    }

    /** @return HasMany<ExampleAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(ExampleAttachment::class)->orderBy('position');
    }
}
