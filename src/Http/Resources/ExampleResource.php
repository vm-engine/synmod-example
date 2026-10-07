<?php

declare(strict_types=1);

namespace VmEngine\Example\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use VmEngine\Example\Models\Example;

/**
 * Public shape of an example. Content and meta are left out on purpose — the
 * API exposes the fields a client lists and filters on.
 *
 * @mixin Example
 */
class ExampleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'slug' => $this->slug,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'category' => $this->whenLoaded('category', fn () => $this->category ? ['id' => $this->category->id, 'name' => $this->category->name] : null),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')->values()->all()),
            'due_at' => $this->due_at?->toDateString(),
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
