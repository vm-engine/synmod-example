<?php

declare(strict_types=1);

namespace VmEngine\Example\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Jodit rich-text editor bound to a Livewire property (deferred, like wire:model).
 * Alpine code: exampleRichText in resources/js/example.js (wired by example:setup).
 * Always purify the submitted HTML server-side (RichTextSanitizer).
 */
class RichText extends Component
{
    public function __construct(
        public string $field,
        public string $value = '',
        public int $height = 360,
    ) {}

    /**
     * @return array{field: string, height: int}
     */
    public function config(): array
    {
        return ['field' => $this->field, 'height' => $this->height];
    }

    public function render(): View
    {
        return view('example::blade.rich-text');
    }
}
