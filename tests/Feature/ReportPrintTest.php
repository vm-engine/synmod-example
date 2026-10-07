<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

it('renders the filtered matrix on the bare print layout', function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $user = User::factory()->create();
    $user->roles()->attach($role);

    $news = ExampleCategory::factory()->create(['name' => 'News']);
    $blog = ExampleCategory::factory()->create(['name' => 'Blog']);
    Example::factory()->create(['category_id' => $news->id]);
    Example::factory()->create(['category_id' => $blog->id]);

    $this->actingAs($user)
        ->get(backend_route('example.report.print', ['filterCategory' => $news->id]))
        ->assertOk()
        ->assertSee('News')
        ->assertDontSee('Blog')
        ->assertSee('examplePrint', false)
        ->assertDontSee('sidebarToggle', false);
});
