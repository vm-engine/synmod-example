<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleAttachment;
use VmEngine\Example\Support\ExampleSettings;
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

it('seeds rows per page from settings', function () {
    ExampleSettings::save(['perPage' => 25]);

    Livewire::actingAs($this->user)->test('example::example-list')->assertSet('perPage', 25);
    Livewire::actingAs($this->user)->test('example::lists.inline-filters')->assertSet('perPage', 25);
});

it('hides the due column when disabled', function () {
    Example::factory()->create(['due_at' => '2026-11-05']);

    Livewire::actingAs($this->user)->test('example::lists.inline-filters')->assertSee('2026-11-05');

    ExampleSettings::save(['showDueColumn' => false]);

    Livewire::actingAs($this->user)->test('example::lists.inline-filters')->assertDontSee('2026-11-05');
});

it('uses the default status for new examples', function () {
    ExampleSettings::save(['defaultStatus' => 'review']);

    Livewire::actingAs($this->user)->test('example::example-editor')->assertSet('form.status', 'review');
    Livewire::actingAs($this->user)->test('example::forms.drawer-form')->call('create')->assertSet('status', 'review');
});

it('caps attachments with the configured maximum', function () {
    Storage::fake('public');
    ExampleSettings::save(['maxAttachments' => 2]);
    $example = Example::factory()->draft()->create();
    ExampleAttachment::factory()->count(2)->for($example)->create();

    Livewire::actingAs($this->user)->test('example::example-editor', ['id' => $example->id])
        ->set('attachments', [UploadedFile::fake()->image('x.png')])
        ->call('save')
        ->assertHasErrors(['attachments' => 'max']);
});
