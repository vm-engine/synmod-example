<?php

declare(strict_types=1);

use VmEngine\Example\Enums\ExampleStatus;

it('has draft, review and published values', function () {
    expect(array_map(fn (ExampleStatus $s): string => $s->value, ExampleStatus::cases()))
        ->toBe(['draft', 'review', 'published']);
});

it('maps each status to a label and a synapse color', function (ExampleStatus $status, string $label, string $color) {
    expect($status->label())->toBe($label)
        ->and($status->color())->toBe($color);
})->with([
    [ExampleStatus::Draft, 'Draft', 'gray'],
    [ExampleStatus::Review, 'In review', 'warning'],
    [ExampleStatus::Published, 'Published', 'success'],
]);

it('builds adv-select options', function () {
    expect(ExampleStatus::options())->toBe([
        ['value' => 'draft', 'label' => 'Draft'],
        ['value' => 'review', 'label' => 'In review'],
        ['value' => 'published', 'label' => 'Published'],
    ]);
});
