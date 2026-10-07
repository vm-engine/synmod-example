<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

/**
 * Pattern page shell for the page patterns.
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait PagePatternPage
{
    use PatternPage;

    public function patternGroup(): string
    {
        return 'pages';
    }
}
