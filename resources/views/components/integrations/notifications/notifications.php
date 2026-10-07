<?php

declare(strict_types=1);

use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\IntegrationPatternPage;
use VmEngine\Example\Notifications\ExampleDemoNotification;
use VmEngine\Synapse\Notifications\Notify;

new class extends Component
{
    use IntegrationPatternPage;

    public bool $withToast = true;

    public function title(): string
    {
        return __('example::integrations.notifications');
    }

    public function sendTest(): void
    {
        Notify::make(new ExampleDemoNotification)->toUsers(auth()->user())->withToastr($this->withToast)->send();

        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::integrations.test_sent'));
    }

    /**
     * The stored shape, shown as an example on the page.
     */
    public function shapeJson(): string
    {
        return (string) json_encode((new ExampleDemoNotification)->toArray(auth()->user()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
};
