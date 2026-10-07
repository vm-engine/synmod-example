<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\IntegrationPatternPage;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

new class extends Component
{
    use GuardsBackendPermission;
    use IntegrationPatternPage;

    public function title(): string
    {
        return __('example::integrations.auth_helpers');
    }

    public function guardedDemo(): void
    {
        if (! $this->guardAction('example.manage.delete')) {
            return;
        }

        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::integrations.guarded_ok'));
    }

    /**
     * @return list<array{call: string, result: bool|string}>
     */
    public function helperResults(): array
    {
        return [
            ['call' => "can_access('example.manage.read')", 'result' => can_access('example.manage.read')],
            ['call' => "can_access('example.manage.delete')", 'result' => can_access('example.manage.delete')],
            ['call' => "can_access('example.settings.update')", 'result' => can_access('example.settings.update')],
            ['call' => "user_can('example.manage', 'update')", 'result' => user_can('example.manage', 'update')],
            ['call' => "has_role('admin')", 'result' => has_role('admin')],
            ['call' => 'is_admin()', 'result' => is_admin()],
            ['call' => 'is_dev()', 'result' => is_dev()],
            ['call' => 'auth_user()?->name', 'result' => (string) auth_user()?->name],
        ];
    }

    /**
     * Directive snippets — built here, because Blade would compile them as
     * directives if they were written in the view.
     *
     * @return array<string, string>
     */
    #[Computed()]
    public function snippets(): array
    {
        return [
            'canAccess' => "@canAccess('example.manage.delete') … @endcanAccess",
            'unlessCanAccess' => "@unlessCanAccess('example.manage.delete') … @endCanAccess",
            'hasRole' => "@hasRole('admin') … @endhasRole",
            'hasAnyRole' => "@hasAnyRole('admin|editor') … @endHasAnyRole",
            'isDev' => '@isDev … @endisDev',
            'isDenied' => "@isDenied('example.manage.delete') … @endIsDenied",
            'hasDirectPermission' => "@hasDirectPermission('example.manage.delete') … @endHasDirectPermission",
            'guard' => "if (! \$this->guardAction('example.manage.delete')) {\n    return;\n}",
        ];
    }
};
