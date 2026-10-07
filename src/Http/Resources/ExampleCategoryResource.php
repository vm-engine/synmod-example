<?php

declare(strict_types=1);

namespace VmEngine\Example\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use VmEngine\Example\Models\ExampleCategory;

/**
 * @mixin ExampleCategory
 */
class ExampleCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
