<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Models\ExampleTag;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = RolePermission::factory()->forModule('example', 'manage')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

function exampleEditor(User $user, ?int $id = null)
{
    return Livewire::actingAs($user)->test('example::example-editor', $id ? ['id' => $id] : []);
}

it('creates an example with every field and redirects to its edit url', function () {
    $category = ExampleCategory::factory()->create();
    $tag = ExampleTag::factory()->create(['name' => 'Laravel']);

    exampleEditor($this->user)
        ->set('form.text', 'Hello Editor')
        ->assertSet('form.slug', 'hello-editor')
        ->set('form.email', 'ed@example.com')
        ->set('form.status', 'review')
        ->set('form.category_id', $category->id)
        ->set('form.color', '#22c55e')
        ->set('form.content', '<p>Body <script>alert(1)</script></p>')
        ->call('addMetaRow')
        ->set('form.meta.0.key', 'source')
        ->set('form.meta.0.value', 'editor')
        ->set('form.tags', [$tag->id, 'Fresh Tag'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $example = Example::query()->where('slug', 'hello-editor')->firstOrFail();

    expect($example->status)->toBe(ExampleStatus::Review)
        ->and($example->content)->toBe('<p>Body </p>')
        ->and($example->meta)->toBe(['source' => 'editor'])
        ->and($example->tags()->pluck('name')->sort()->values()->all())->toBe(['Fresh Tag', 'Laravel']);
});

it('stops following the title once the slug is edited by hand', function () {
    exampleEditor($this->user)
        ->set('form.text', 'First')
        ->assertSet('form.slug', 'first')
        ->set('form.slug', 'custom-slug')
        ->set('form.text', 'Second')
        ->assertSet('form.slug', 'custom-slug');
});

it('validates on blur and rejects a duplicate slug', function () {
    Example::factory()->create(['slug' => 'taken']);

    exampleEditor($this->user)
        ->set('form.email', 'not-an-email')
        ->assertHasErrors(['form.email' => 'email'])
        ->set('form.slug', 'taken')
        ->assertHasErrors(['form.slug' => 'unique']);
});

it('requires a publish note only when published and a due date only when scheduled', function () {
    $component = exampleEditor($this->user)
        ->set('form.text', 'Rules')
        ->set('form.email', 'r@example.com')
        ->set('form.status', 'published')
        ->set('form.schedule', true)
        ->call('save')
        ->assertHasErrors(['form.publish_note' => 'required_if', 'form.due_at' => 'required_if_accepted']);

    $component->set('form.publish_note', 'Ship it')->set('form.due_at', '2026-11-01')->call('save')->assertHasNoErrors();

    expect(Example::query()->where('slug', 'rules')->first()->meta)->toBe(['publish_note' => 'Ship it']);
});

it('adds, removes and reorders meta rows', function () {
    $component = exampleEditor($this->user)
        ->call('addMetaRow')->set('form.meta.0.key', 'a')
        ->call('addMetaRow')->set('form.meta.1.key', 'b')
        ->call('addMetaRow')->set('form.meta.2.key', 'c')
        ->call('moveMetaRow', 2, 0);

    expect(array_column($component->get('form.meta'), 'key'))->toBe(['c', 'a', 'b']);

    $component->call('removeMetaRow', 1);
    expect(array_column($component->get('form.meta'), 'key'))->toBe(['c', 'b']);
});

it('round-trips meta through the raw JSON view and rejects invalid JSON', function () {
    $component = exampleEditor($this->user)
        ->call('addMetaRow')->set('form.meta.0.key', 'k')->set('form.meta.0.value', 'v')
        ->call('toggleRawJson')
        ->assertSet('form.rawJson', true);

    expect(json_decode($component->get('form.metaJson'), true))->toBe(['k' => 'v']);

    $component->set('form.metaJson', '{"x": "1", "y": "2"}')->call('toggleRawJson')->assertSet('form.rawJson', false);
    expect($component->get('form.meta'))->toBe([['key' => 'x', 'value' => '1'], ['key' => 'y', 'value' => '2']]);

    $component->call('toggleRawJson')->set('form.metaJson', '[1, 2]')->call('toggleRawJson')
        ->assertHasErrors('form.metaJson')
        ->assertSet('form.rawJson', true);
});

it('searches and preloads tags for the creatable select', function () {
    $tag = ExampleTag::factory()->create(['name' => 'Livewire']);

    $component = exampleEditor($this->user);

    expect($component->instance()->searchTags('live'))->toBe([['value' => $tag->id, 'label' => 'Livewire']])
        ->and($component->instance()->loadTags([$tag->id, 'new one']))->toBe([['value' => $tag->id, 'label' => 'Livewire']]);
});

it('loads an existing example for editing', function () {
    $example = Example::factory()->published()->create(['meta' => ['publish_note' => 'n', 'k' => 'v'], 'due_at' => '2026-12-01']);

    exampleEditor($this->user, $example->id)
        ->assertSet('form.slug', $example->slug)
        ->assertSet('form.publish_note', 'n')
        ->assertSet('form.meta', [['key' => 'k', 'value' => 'v']])
        ->assertSet('form.schedule', true)
        ->assertSet('form.due_at', '2026-12-01');
});

it('redirects when the example does not exist', function () {
    exampleEditor($this->user, 9999)->assertRedirect();
});

it('refuses to save without permission', function () {
    $readOnly = User::factory()->create();
    $readOnly->roles()->attach(Role::factory()->has(RolePermission::factory()->forModule('example', 'manage')->readOnly())->create());

    Livewire::actingAs($readOnly)
        ->test('example::example-editor')
        ->set('form.text', 'Nope')->set('form.email', 'n@example.com')
        ->call('save')
        ->assertDispatched('notify');

    expect(Example::query()->where('text', 'Nope')->exists())->toBeFalse();
});

/*
 * Livewire 4.1: wire:model.blur only syncs client state on blur; it no longer
 * sends a request. Blur validation and slug auto-fill need .live.blur.
 */
it('sends blur-validated fields to the server on blur', function () {
    $this->actingAs($this->user)
        ->get(backend_route('example.editor'))
        ->assertOk()
        ->assertSee('wire:model.live.blur="form.text"', false)
        ->assertSee('wire:model.live.blur="form.slug"', false)
        ->assertSee('wire:model.live.blur="form.email"', false);
});
