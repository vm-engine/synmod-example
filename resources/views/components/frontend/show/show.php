<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\FrontendPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use FrontendPage;

    #[Locked]
    public int $exampleId;

    public function mount(string $slug): void
    {
        $this->exampleId = Example::query()->published()->where('slug', $slug)->valueOrFail('id');
    }

    #[Computed()]
    public function example(): Example
    {
        return Example::query()->published()->with(['category', 'tags', 'attachments'])->findOrFail($this->exampleId);
    }

    public function pageTitle(): string
    {
        return $this->example->text;
    }

    public function pageDescription(): string
    {
        return str((string) $this->example->content)->stripTags()->squish()->limit(155)->value() ?: __('example::frontend.description');
    }

    #[Computed()]
    public function coverUrl(): ?string
    {
        $file = (string) $this->example->file;

        return $file !== '' && Storage::disk('public')->exists($file) ? Storage::disk('public')->url($file) : null;
    }

    /**
     * @return list<array{url: string, name: string}>
     */
    #[Computed()]
    public function images(): array
    {
        return $this->example->attachments
            ->filter(fn ($attachment): bool => $attachment->isImage())
            ->map(fn ($attachment): array => ['url' => $attachment->url(), 'name' => $attachment->original_name])
            ->values()
            ->all();
    }
};
