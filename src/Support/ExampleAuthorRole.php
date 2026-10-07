<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;

/**
 * The ABAC demo role: read/create every example, update/delete only your own.
 *
 * The plain example.manage row (sub_feature null) grants read + create; the
 * conditional row (sub_feature '*', so it can sit next to it under the unique
 * key) grants update + delete when created_by = the acting user. A conditional
 * row only counts when the record is passed to can() — record-less checks
 * fail closed.
 */
final class ExampleAuthorRole
{
    public const SLUG = 'example-author';

    /** @var array<string, mixed> */
    public const OWN_RECORDS = ['field' => 'created_by', 'operator' => 'eq', 'value' => '$user.id'];

    /**
     * Create the role and its two permission rows if missing (never overwrites).
     */
    public static function ensure(): Role
    {
        $role = Role::query()->firstOrCreate(['slug' => self::SLUG], [
            'name' => 'Example author',
            'level' => 10,
            'description' => 'Example module ABAC demo: edits and deletes only the examples they created.',
            'can_access_frontend' => true,
            'can_access_backend' => true,
        ]);

        RolePermission::query()->firstOrCreate(
            ['role_id' => $role->id, 'module_slug' => 'example', 'feature_slug' => 'manage', 'sub_feature_slug' => null],
            ['can_create' => true, 'can_read' => true, 'can_update' => false, 'can_delete' => false],
        );

        RolePermission::query()->firstOrCreate(
            ['role_id' => $role->id, 'module_slug' => 'example', 'feature_slug' => 'manage', 'sub_feature_slug' => '*'],
            ['condition' => self::OWN_RECORDS, 'can_create' => false, 'can_read' => false, 'can_update' => true, 'can_delete' => true],
        );

        return $role;
    }
}
