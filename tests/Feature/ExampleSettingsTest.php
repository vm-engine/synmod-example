<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Support\ExampleSettings;

uses(RefreshDatabase::class);

it('returns defaults when nothing is stored', function () {
    expect(ExampleSettings::all())->toBe([
        'perPage' => 15,
        'showDueColumn' => true,
        'defaultStatus' => 'draft',
        'maxAttachments' => 10,
        'wipLimit' => 0,
    ]);
});

it('saves and reads values through dbconf', function () {
    ExampleSettings::save(['perPage' => 25, 'wipLimit' => 3, 'unknown' => 'x']);

    expect(ExampleSettings::perPage())->toBe(25)
        ->and(ExampleSettings::wipLimit())->toBe(3)
        ->and(ExampleSettings::defaultStatus())->toBe('draft')
        ->and(json_decode((string) dbconf('!example.settings'), true))->not->toHaveKey('unknown');
});

it('falls back per key when stored values are invalid', function () {
    dbconf(['example.settings' => json_encode(['perPage' => 7, 'showDueColumn' => 'yes', 'defaultStatus' => 'archived', 'maxAttachments' => 500, 'wipLimit' => 5])]);

    expect(ExampleSettings::all())->toBe([
        'perPage' => 15,
        'showDueColumn' => true,
        'defaultStatus' => 'draft',
        'maxAttachments' => 10,
        'wipLimit' => 5,
    ]);
});

it('falls back entirely on broken JSON', function () {
    dbconf(['example.settings' => '{not json']);

    expect(ExampleSettings::maxAttachments())->toBe(10);
});
