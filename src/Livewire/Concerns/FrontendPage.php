<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

/**
 * Public page on the active theme's frontend layout (same resolution as
 * synmod-cms), with a page title and meta description.
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait FrontendPage
{
    abstract public function pageTitle(): string;

    public function pageDescription(): string
    {
        return __('example::frontend.description');
    }

    public function render()
    {
        $layout = theme_service()->resolveThemeLayout('frontend');

        return $this->view()
            ->title(page_title($this->pageTitle()))
            ->layoutData(['description' => $this->pageDescription()])
            ->layout(view()->exists($layout) ? $layout : 'synapps::components.layouts.layout');
    }
}
