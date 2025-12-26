<?php

namespace VmEngine\Example\Livewire;

use Livewire\Component;

class ExamplePage extends Component
{
    public function render()
    {
        return view('example::livewire.example-page')
            ->title(__('example::menu.be.parent'));
    }
}
