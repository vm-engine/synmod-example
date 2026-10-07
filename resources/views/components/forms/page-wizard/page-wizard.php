<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\FormPatternPage;
use VmEngine\Example\Livewire\Concerns\HasTagSearch;
use VmEngine\Example\Livewire\Concerns\WizardSteps;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\RichTextSanitizer;
use VmEngine\Example\Support\TagResolver;

new class extends Component
{
    use FormPatternPage;
    use HasTagSearch;
    use WizardSteps;

    public string $text = '';

    public string $email = '';

    public ?int $category_id = null;

    public string $content = '';

    public string $color = '#465fff';

    /** @var list<int|string> */
    public array $tags = [];

    public function title(): string
    {
        return __('example::forms.page_wizard');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function stepRules(): array
    {
        return [
            1 => [
                'text' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'category_id' => ['nullable', 'integer', 'exists:example_categories,id'],
            ],
            2 => [
                'content' => ['nullable', 'string', 'max:20000'],
                'color' => ['nullable', 'hex_color'],
            ],
            3 => [
                'tags' => ['array', 'max:10'],
            ],
            4 => [],
        ];
    }

    public function finish(): void
    {
        if (! auth()->user()?->can('example.manage.create')) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::forms.not_allowed'));

            return;
        }

        $this->validate($this->allStepRules());

        $example = DB::transaction(function (): Example {
            $example = Example::query()->create([
                'text' => $this->text,
                'email' => $this->email,
                'category_id' => $this->category_id,
                'content' => RichTextSanitizer::clean($this->content),
                'color' => $this->color ?: null,
            ]);
            $example->tags()->sync(TagResolver::resolve($this->tags));

            return $example;
        });

        session()->flash('success', __('example::forms.created'));
        $this->redirect(backend_route('example.editor', ['id' => $example->id]), navigate: true);
    }

    /**
     * @return list<string>
     */
    #[Computed()]
    public function stepLabels(): array
    {
        return [__('example::forms.step_basics'), __('example::forms.step_content'), __('example::forms.step_tags'), __('example::forms.step_review')];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    #[Computed()]
    public function categories(): array
    {
        return ExampleCategory::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (ExampleCategory $category): array => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }

    /**
     * Review step summary (content as plain text, tags as names / new names).
     *
     * @return array{category: string|null, content: string, tags: list<string>}
     */
    #[Computed()]
    public function review(): array
    {
        $existing = $this->loadTags($this->tags);
        $names = array_column($existing, 'label');

        foreach ($this->tags as $tag) {
            if (is_string($tag) && ! ctype_digit($tag)) {
                $names[] = trim($tag);
            }
        }

        return [
            'category' => $this->category_id ? ExampleCategory::query()->whereKey($this->category_id)->value('name') : null,
            'content' => Str::limit(trim(strip_tags($this->content)), 300),
            'tags' => array_values(array_unique($names)),
        ];
    }
};
