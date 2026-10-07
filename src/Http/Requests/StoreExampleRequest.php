<?php

declare(strict_types=1);

namespace VmEngine\Example\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use VmEngine\Example\Enums\ExampleStatus;

/**
 * Signed create over the API — the editor's core rules.
 */
class StoreExampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'status' => ['required', Rule::enum(ExampleStatus::class)],
            'category_id' => ['nullable', 'integer', 'exists:example_categories,id'],
            'due_at' => ['nullable', 'date_format:Y-m-d'],
            'publish_note' => ['nullable', 'string', 'max:500', Rule::requiredIf($this->input('status') === ExampleStatus::Published->value)],
        ];
    }
}
