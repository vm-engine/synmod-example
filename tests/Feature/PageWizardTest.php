<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
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

it('walks four steps, never validates on back, and creates with tags', function () {
    $tag = ExampleTag::factory()->create(['name' => 'UI']);

    Livewire::actingAs($this->user)
        ->test('example::forms.page-wizard')
        ->set('text', 'Paged')
        ->set('email', 'p@example.com')
        ->call('next')
        ->assertSet('step', 2)
        ->set('color', 'blue')
        ->call('next')
        ->assertHasErrors(['color' => 'hex_color'])
        ->call('back')
        ->assertHasNoErrors()
        ->assertSet('step', 1)
        ->call('next')
        ->set('color', '#3b82f6')
        ->set('content', '<p>Hi<script>x</script></p>')
        ->call('next')
        ->assertSet('step', 3)
        ->set('tags', [$tag->id, 'Wizardry'])
        ->call('next')
        ->assertSet('step', 4)
        ->assertSee('Paged')
        ->call('finish')
        ->assertRedirect();

    $example = Example::query()->where('text', 'Paged')->firstOrFail();

    expect($example->content)->toBe('<p>Hi</p>')
        ->and($example->tags()->pluck('name')->sort()->values()->all())->toBe(['UI', 'Wizardry']);
});
