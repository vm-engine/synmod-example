<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use VmEngine\Accounting\Accounting;
use VmEngine\Accounting\Enums\JournalStatus;
use VmEngine\Accounting\Enums\NormalBalance;
use VmEngine\Accounting\Integration\IntegrationRegistry;
use VmEngine\Accounting\Models\Account;
use VmEngine\Accounting\Models\AccountMapping;
use VmEngine\Accounting\Models\FiscalPeriod;
use VmEngine\Accounting\Models\JournalEntry;
use VmEngine\Accounting\Models\PendingPosting;
use VmEngine\Example\Catalog\PatternCatalog;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
    $this->example = Example::factory()->create();
    $this->page = fn () => Livewire::actingAs($this->user)->test('example::integrations.accounting')->set('exampleId', (string) $this->example->id)->set('amount', '250');
});

it('offers the pattern only where accounting is installed', function () {
    $installed = class_exists(Accounting::class);

    expect(Route::has('backend.example.integrations.accounting'))->toBe($installed)
        ->and(in_array('int-accounting', array_column(PatternCatalog::patterns(), 'key'), true))->toBe($installed);
});

// Accounting is an optional dependency of this module: these run only where it is installed.
describe('with accounting installed', function () {
    beforeEach(function () {
        if (! class_exists(Accounting::class)) {
            $this->markTestSkipped('Needs vm-engine/synmod-accounting.');
        }

        FiscalPeriod::query()->create(['year' => (int) now()->year, 'start_date' => now()->startOfYear()->toDateString(), 'end_date' => now()->endOfYear()->toDateString(), 'is_annual' => true]);
        $this->cash = Account::query()->create(['code' => 'T.1', 'name' => 'Cash', 'level' => 0, 'path' => ',', 'normal_balance' => NormalBalance::Debit]);
        $this->revenue = Account::query()->create(['code' => 'T.4', 'name' => 'Revenue', 'level' => 0, 'path' => ',', 'normal_balance' => NormalBalance::Credit]);
    });

    it('renders the page with the sample code', function () {
        $this->actingAs($this->user)->get(backend_route('example.integrations.accounting'))->assertOk()->assertSee('Accounting::journal');
    });

    it('declares its class and account settings in module.json', function () {
        $registry = app(IntegrationRegistry::class);

        expect($registry->classes()['example.sale'])->toBe(['module' => 'example', 'name' => 'Example sale', 'types' => ['cash' => 'Cash sale', 'transfer' => 'Transfer sale']])
            ->and(array_keys($registry->accounts()))->toContain('example.cash', 'example.revenue');
    });

    it('is held while the account settings are not mapped', function () {
        ($this->page)()->call('post')->assertHasNoErrors()->assertSee(__('accounting::labels.posting_outcomes.held'));

        expect(PendingPosting::query()->sole()->source()->key())->toBe('example:example:'.$this->example->id)
            ->and(JournalEntry::query()->count())->toBe(0);
    });

    it('posts, replaces and cancels once the settings are mapped', function () {
        AccountMapping::query()->create(['key' => 'example.cash', 'module' => 'example', 'account_id' => $this->cash->id]);
        AccountMapping::query()->create(['key' => 'example.revenue', 'module' => 'example', 'account_id' => $this->revenue->id]);

        ($this->page)()->call('post')->assertSee(__('accounting::labels.posting_outcomes.posted'));
        $journal = JournalEntry::query()->sole();
        expect($journal->only(['source_module', 'source_type', 'source_id', 'reference']))
            ->toBe(['source_module' => 'example', 'source_type' => 'example', 'source_id' => $this->example->id, 'reference' => 'EX-'.$this->example->id])
            ->and($journal->lines()->sum('debit'))->toEqual(250);

        ($this->page)()->set('amount', '300')->call('replace');
        expect($journal->refresh()->status)->toBe(JournalStatus::Edited)
            ->and($journal->adjustingEntry->lines()->sum('debit'))->toEqual(300);

        ($this->page)()->call('cancel')->assertSee(__('accounting::labels.posting_outcomes.cancelled'));
        expect($journal->refresh()->status)->toBe(JournalStatus::Unposted);
    });

    it('validates the amount', function () {
        ($this->page)()->set('amount', '0')->call('post')->assertHasErrors('amount');
    });
});
