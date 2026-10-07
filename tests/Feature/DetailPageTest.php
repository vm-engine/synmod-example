<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleAttachment;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('shows the overview with purified content and meta', function () {
    $example = Example::factory()->create(['text' => 'Shown', 'content' => '<p>Body</p>', 'meta' => ['source' => 'seed']]);

    Livewire::actingAs($this->user)
        ->test('example::pages.detail', ['id' => $example->id])
        ->assertSee('Shown')
        ->assertSeeHtml('<p>Body</p>')
        ->assertSee('source');
});

it('keeps the tab in a whitelist and mounts lazy children per tab', function () {
    $example = Example::factory()->create();

    Livewire::withQueryParams(['tab' => 'nope'])->actingAs($this->user)
        ->test('example::pages.detail', ['id' => $example->id])
        ->assertSet('tab', 'overview')
        ->call('selectTab', 'attachments')
        ->assertSet('tab', 'attachments')
        ->assertSeeHtml('detail-attachments');
});

it('opens the latest example without an id and redirects for a missing one', function () {
    Livewire::actingAs($this->user)->test('example::pages.detail')->assertSee(__('example::pages.no_examples'));

    $latest = Example::factory()->create(['text' => 'Newest']);
    Livewire::actingAs($this->user)->test('example::pages.detail')->assertSet('exampleId', $latest->id);

    Livewire::actingAs($this->user)->test('example::pages.detail', ['id' => 9999])->assertRedirect();
});

it('lists and deletes attachments in the attachments tab', function () {
    Storage::fake('public');
    $example = Example::factory()->create();
    $attachment = ExampleAttachment::factory()->for($example)->create(['original_name' => 'spec.pdf', 'mime' => 'application/pdf']);

    Livewire::actingAs($this->user)
        ->test('example::pages.detail-attachments', ['exampleId' => $example->id])
        ->assertSee('spec.pdf')
        ->call('deleteAttachment', $attachment->delete_token)
        ->assertDontSee('spec.pdf');

    expect(ExampleAttachment::query()->find($attachment->id))->toBeNull();
});

it('lists other examples in the same category', function () {
    $category = ExampleCategory::factory()->create();
    $example = Example::factory()->create(['category_id' => $category->id]);
    Example::factory()->create(['category_id' => $category->id, 'text' => 'Sibling']);
    Example::factory()->create(['text' => 'Stranger']);

    Livewire::actingAs($this->user)
        ->test('example::pages.detail-related', ['exampleId' => $example->id])
        ->assertSee('Sibling')
        ->assertDontSee('Stranger');
});
