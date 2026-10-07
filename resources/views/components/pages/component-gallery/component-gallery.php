<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\PatternPage;
use VmEngine\Example\Models\ExampleAttachment;

new class extends Component
{
    use PatternPage;

    private const TOAST_VARIANTS = ['success', 'info', 'warning', 'danger'];

    public function title(): string
    {
        return __('example::components.gallery');
    }

    public function patternGroup(): string
    {
        return 'components';
    }

    public function toast(string $variant): void
    {
        $variant = in_array($variant, self::TOAST_VARIANTS, true) ? $variant : 'info';

        $this->dispatch('notify', variant: $variant, title: ucfirst($variant), message: __('example::components.toast_message', ['variant' => $variant]));
    }

    public function confirmedDemo(): void
    {
        // Closes x-synapse-confirm-dialog on every path.
        $this->dispatch('synapse-confirmed');

        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::components.confirmed'));
    }

    /**
     * @return list<string>
     */
    #[Computed()]
    public function toastVariants(): array
    {
        return self::TOAST_VARIANTS;
    }

    /**
     * Latest uploaded image, else the host favicon.
     */
    #[Computed()]
    public function lightboxImage(): string
    {
        $image = ExampleAttachment::query()->where('mime', 'like', 'image/%')->latest('id')->first();

        return $image?->url() ?? asset('favicon.svg');
    }

    /**
     * @return array<string, string>
     */
    #[Computed()]
    public function snippets(): array
    {
        return [
            'alerts' => '<x-synapse-alert type="success" message="Saved!" :dismissible="true" />',
            'badges' => '<x-synapse-badge color="success" size="sm" icon="ph ph-check">Published</x-synapse-badge>',
            'panels' => '<x-synapse-panel title="Title" collapsible>…</x-synapse-panel>',
            'stat_tiles' => '<x-synapse-stat-tile icon="ph ph-cube" color="blue" label="Total" value="1,234" />',
            'overlays' => "<x-synapse-lightbox name=\"demo\" /> · \$this->dispatch('notify', …) · \$dispatch('confirm-dialog', {…}) · <x-synapse-drawer>",
        ];
    }
};
