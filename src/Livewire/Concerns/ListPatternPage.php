<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

/**
 * Pattern page shell for the list patterns.
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait ListPatternPage
{
    use PatternPage;

    public function patternGroup(): string
    {
        return 'lists';
    }
}
