<?php

declare(strict_types=1);

namespace VmEngine\Example;

use Illuminate\Support\ServiceProvider;
use VmEngine\Synapse\Traits\AutoRegistersComponents;

class ExampleServiceProvider extends ServiceProvider
{
    use AutoRegistersComponents;

    public function register(): void
    {
        $this->registerComponents();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'example');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'example');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
