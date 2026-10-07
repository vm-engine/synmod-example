<?php

declare(strict_types=1);

namespace VmEngine\Example;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use VmEngine\Example\Console\SearchReindexCommand;
use VmEngine\Example\Console\SetupCommand;
use VmEngine\Example\Listeners\NotifyExampleImportFinished;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\UserDefaultCategory;
use VmEngine\Synapse\Events\ExcelImportCompleted;
use VmEngine\Synapse\Traits\AutoRegistersComponents;
use VmEngine\SynAuth\Facades\SynAuthExtension;
use VmEngine\SynAuth\Livewire\Backend\UserForm;

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
        $this->registerUserFieldExtension();
        Event::listen(ExcelImportCompleted::class, NotifyExampleImportFinished::class);

        if ($this->app->runningInConsole()) {
            $this->commands([SetupCommand::class, SearchReindexCommand::class]);
        }
    }

    /**
     * User field extension demo (synapps-auth): a default example category on
     * the backend user form, read by UserDefaultCategory.
     */
    private function registerUserFieldExtension(): void
    {
        SynAuthExtension::registerField(UserDefaultCategory::FIELD, ['type' => 'foreignId', 'nullable' => true, 'cast' => 'integer']);

        SynAuthExtension::registerValidation(UserForm::class, [
            UserDefaultCategory::FIELD => ['nullable', 'integer', 'exists:example_categories,id'],
        ]);

        SynAuthExtension::registerFormHook(UserForm::class, fn (): string => view('example::partials.user-default-category', [
            'categories' => ExampleCategory::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (ExampleCategory $category): array => ['value' => $category->id, 'label' => $category->name])
                ->all(),
        ])->render());
    }
}
