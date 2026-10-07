<?php

declare(strict_types=1);

namespace VmEngine\Example\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use VmEngine\Example\Enums\ExampleStatus;

/**
 * Status change over the API. Same rule as the editor: Published needs a note.
 * Authorization is the route's api.permission middleware.
 */
class UpdateExampleStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(ExampleStatus::class)],
            'publish_note' => ['nullable', 'string', 'max:500', Rule::requiredIf($this->input('status') === ExampleStatus::Published->value)],
        ];
    }
}
