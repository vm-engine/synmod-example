<?php

declare(strict_types=1);

use Livewire\Component;

new class extends Component
{
    public function title(): string
    {
        return __('example::menu.be.parent');
    }
};
