<?php

declare(strict_types=1);

namespace VmEngine\Example\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use VmEngine\Example\Factories\ExampleAttachmentFactory;

/**
 * @property int $id
 * @property int $example_id
 * @property string $path
 * @property string $original_name
 * @property string $mime
 * @property int $size
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Example $example
 */
class ExampleAttachment extends Model
{
    /** @use HasFactory<ExampleAttachmentFactory> */
    use HasFactory;

    protected $fillable = ['example_id', 'path', 'original_name', 'mime', 'size', 'position'];

    protected $casts = [
        'size' => 'integer',
    ];

    protected static function newFactory(): ExampleAttachmentFactory
    {
        return ExampleAttachmentFactory::new();
    }

    protected static function booted(): void
    {
        static::deleted(function (ExampleAttachment $attachment): void {
            Storage::disk('public')->delete($attachment->path);
        });
    }

    /** @return BelongsTo<Example, $this> */
    public function example(): BelongsTo
    {
        return $this->belongsTo(Example::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }
}
