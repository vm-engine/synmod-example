<?php

namespace VmEngine\Example;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use VmEngine\Example\Livewire\CategoryForm;
use VmEngine\Example\Livewire\CategoryList;
use VmEngine\Example\Livewire\Components\ButtonSample;
use VmEngine\Example\Livewire\ExampleForm;
use VmEngine\Example\Livewire\ExampleList;

class ExampleServiceProvider extends ServiceProvider
{
    public function register()
    {
        Livewire::component('example::button-sample', ButtonSample::class);
        Livewire::component('example-list', ExampleList::class);
        Livewire::component('example-form', ExampleForm::class);
        Livewire::component('category-list', CategoryList::class);
        Livewire::component('category-form', CategoryForm::class);
    }

    public function boot() {}
}
