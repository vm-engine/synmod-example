<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

it('can create an example', function () {
    $example = Example::factory()->create([
        'text' => 'Test Text',
        'email' => 'test@example.com',
    ]);

    expect($example)
        ->text->toBe('Test Text')
        ->email->toBe('test@example.com');

    $this->assertDatabaseHas('examples', [
        'text' => 'Test Text',
        'email' => 'test@example.com',
    ]);
});

it('renders the example form', function () {
    $permission = RolePermission::factory()
        ->forModule('example', 'manage')
        ->fullCrud();
    $adminRole = Role::factory()->has($permission)->create([
        'name' => 'Administrator',
        'slug' => 'admin',
        'level' => 5,
    ]);
    $user = User::factory()->create();
    $user->roles()->attach($adminRole);

    $response = $this->actingAs($user)->get(route('backend.example.form'));

    $response->assertStatus(200);
});

it('renders the example list', function () {
    $permission = RolePermission::factory()
        ->forModule('example', 'manage')
        ->fullCrud();
    $adminRole = Role::factory()->has($permission)->create([
        'name' => 'Administrator',
        'slug' => 'admin',
        'level' => 5,
    ]);
    $user = User::factory()->create();
    $user->roles()->attach($adminRole);

    $response = $this->actingAs($user)->get(route('backend.example.index'));

    $response->assertStatus(200);
});
