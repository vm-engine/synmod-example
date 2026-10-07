<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    foreach (['manage', 'category', 'tag', 'node'] as $feature) {
        RolePermission::factory()->forModule('example', $feature)->fullCrud()->create(['role_id' => $role->id]);
    }
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

/*
 * x-synapse-confirm-dialog never closes itself on Confirm — it waits for the
 * `synapse-confirmed` browser event. Every wireMethod it calls must dispatch
 * it on every path, or the dialog stays open over the page.
 */
it('closes the confirm dialog from every confirmed action', function (string $component, string $method, array $params) {
    Livewire::actingAs($this->user)
        ->test($component, $component === 'example::pages.detail-attachments' ? ['exampleId' => Example::factory()->create()->id] : [])
        ->call($method, ...$params)
        ->assertDispatched('synapse-confirmed');
})->with([
    'example list delete' => ['example::example-list', 'delete', ['bogus']],
    'category delete' => ['example::category-list', 'delete', ['bogus']],
    'tag delete' => ['example::tag-list', 'delete', ['bogus']],
    'node delete' => ['example::node-tree', 'delete', ['bogus']],
    'trash delete' => ['example::lists.trashed', 'delete', ['bogus']],
    'trash force delete' => ['example::lists.trashed', 'forceDelete', ['bogus']],
    'bulk delete' => ['example::lists.bulk-actions', 'bulkDelete', []],
    'attachment delete' => ['example::pages.detail-attachments', 'deleteAttachment', ['bogus']],
    'gallery demo' => ['example::pages.component-gallery', 'confirmedDemo', []],
]);
