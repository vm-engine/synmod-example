<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\RichTextSanitizer;
use VmEngine\Example\Support\TagResolver;

/**
 * Validation + persistence for the full editor and the tabbed form.
 */
class ExampleEditorForm extends Form
{
    public ?Example $example = null;

    public string $text = '';

    public string $slug = '';

    public bool $slugTouched = false;

    public string $email = '';

    public string $status = 'draft';

    public ?int $category_id = null;

    public bool $schedule = false;

    public string $due_at = '';

    public string $color = '#465fff';

    public string $content = '';

    public string $publish_note = '';

    /** @var list<array{key: string, value: string}> */
    public array $meta = [];

    public bool $rawJson = false;

    public string $metaJson = '';

    /** @var list<int|string> */
    public array $tags = [];

    public function setExample(Example $example): void
    {
        $this->example = $example;
        $this->text = $example->text;
        $this->slug = $example->slug;
        $this->slugTouched = true;
        $this->email = $example->email;
        $this->status = $example->status->value;
        $this->category_id = $example->category_id;
        $this->schedule = $example->due_at !== null;
        $this->due_at = $example->due_at?->toDateString() ?? '';
        $this->color = $example->color ?: '#465fff';
        $this->content = (string) $example->content;

        $meta = $example->meta ?? [];
        $this->publish_note = is_string($meta['publish_note'] ?? null) ? $meta['publish_note'] : '';
        unset($meta['publish_note']);
        $this->meta = $this->rowsFrom($meta);

        $this->tags = array_values($example->tags()->pluck('example_tags.id')->map(fn ($id): int => (int) $id)->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('examples', 'slug')->ignore($this->example?->id)],
            'email' => ['required', 'email', 'max:255'],
            'status' => ['required', Rule::enum(ExampleStatus::class)],
            'category_id' => ['nullable', 'integer', 'exists:example_categories,id'],
            'schedule' => ['boolean'],
            'due_at' => ['nullable', 'required_if_accepted:schedule', 'date_format:Y-m-d'],
            'color' => ['nullable', 'hex_color'],
            'content' => ['nullable', 'string', 'max:20000'],
            'publish_note' => ['nullable', 'required_if:status,published', 'string', 'max:500'],
            'meta' => ['array', 'max:20'],
            'meta.*.key' => ['required', 'string', 'max:50', 'distinct'],
            'meta.*.value' => ['nullable', 'string', 'max:500'],
            'tags' => ['array', 'max:10'],
            'tags.*' => [function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_int($value) && ! (is_string($value) && trim($value) !== '' && mb_strlen(trim($value)) <= 50)) {
                    $fail(__('validation.string', ['attribute' => 'tag']));
                }
            }],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'text' => __('example::forms.text'),
            'slug' => __('example::forms.slug'),
            'email' => __('example::forms.email'),
            'due_at' => __('example::forms.due_at'),
            'publish_note' => __('example::forms.publish_note'),
            'meta.*.key' => __('example::forms.meta_key'),
            'meta.*.value' => __('example::forms.meta_value'),
        ];
    }

    /**
     * Switch between the repeater and raw JSON views of the same meta data.
     */
    public function toggleRawJson(): void
    {
        if (! $this->rawJson) {
            $this->metaJson = (string) json_encode($this->keyedMeta(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
            $this->rawJson = true;

            return;
        }

        $this->meta = $this->parseJson();
        $this->rawJson = false;
    }

    public function save(): Example
    {
        if ($this->rawJson) {
            $this->meta = $this->parseJson();
        }

        // Normalize before validating so uniqueness is checked on the stored value.
        $this->slug = Str::lower(trim($this->slug));

        $this->validate();

        return DB::transaction(function (): Example {
            $example = $this->example ?? new Example;

            $meta = $this->keyedMeta();
            if ($this->status === ExampleStatus::Published->value) {
                $meta['publish_note'] = trim($this->publish_note);
            }

            $example->fill([
                'text' => $this->text,
                'slug' => $this->slug,
                'email' => $this->email,
                'status' => $this->status,
                'category_id' => $this->category_id,
                'due_at' => $this->schedule ? $this->due_at : null,
                'color' => $this->color ?: null,
                'content' => RichTextSanitizer::clean($this->content),
                'meta' => $meta === [] ? null : $meta,
            ])->save();

            $example->tags()->sync(TagResolver::resolve($this->tags));

            return $this->example = $example;
        });
    }

    /**
     * @return array<string, string>
     */
    private function keyedMeta(): array
    {
        $keyed = [];

        foreach ($this->meta as $row) {
            $key = trim($row['key']);

            if ($key !== '') {
                $keyed[$key] = $row['value'];
            }
        }

        return $keyed;
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    private function parseJson(): array
    {
        $decoded = json_decode($this->metaJson === '' ? '{}' : $this->metaJson, true);

        if (! is_array($decoded) || ($decoded !== [] && array_is_list($decoded))) {
            throw ValidationException::withMessages(['form.metaJson' => __('example::forms.json_invalid')]);
        }

        foreach ($decoded as $value) {
            if (! is_scalar($value) && $value !== null) {
                throw ValidationException::withMessages(['form.metaJson' => __('example::forms.json_invalid')]);
            }
        }

        return $this->rowsFrom($decoded);
    }

    /**
     * @param  array<mixed>  $keyed
     * @return list<array{key: string, value: string}>
     */
    private function rowsFrom(array $keyed): array
    {
        $rows = [];

        foreach ($keyed as $key => $value) {
            $rows[] = ['key' => (string) $key, 'value' => is_scalar($value) ? (string) $value : ''];
        }

        return $rows;
    }
}
