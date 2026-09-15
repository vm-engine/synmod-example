# Strict Types Plan — synmod-example

Same pattern as `packages/synapse` (done — see that package's `phpstan.neon`, now level 6, 0 errors, 503 tests green). Not implemented here yet — inventory only.

## Scope

- 26 in-repo PHP files.
- 19 missing `declare(strict_types=1)` — most of the package: `database/seeders` (3), `database/migrations` (3), `tests/Feature` (2), `src/Models` (2), `src/Livewire` (2), `database/factories` (2), plus route/lang files.
- Has its own `phpstan.neon` at **level 5**. Per `CLAUDE.md`, still run Pint/PHPStan/Pest for this module from the **main Laravel project root** (not `vendor/bin/phpstan` inside the package) — but bump this package's own `phpstan.neon` `level: 5` → `level: 6` too, to match the depth chosen for synapse, since this module is the reference implementation other module authors copy patterns from (per project CLAUDE.md's "Example Module Reference" section).

## Steps (mirror the synapse execution)

1. Bump `packages/synmod-example/phpstan.neon` `level: 5` → `level: 6`.
2. Add `declare(strict_types=1);` to the 19 files above.
3. `php -l` sweep + run this module's Pest suite from main root.
4. `vendor/bin/phpstan analyse packages/synmod-example --error-format=raw --no-progress` from main root; fix findings (mostly likely `src/Livewire` and `src/Models` param/return types given synapse's error shape).
5. `vendor/bin/pint packages/synmod-example --format agent` from main root.
6. Re-run Pest to confirm green — this module is the pattern reference for `ExampleForm.php`/`ExampleList.php`/`ExampleCategoryForm.php` used across the project's form-button-placement rules, so its final typed shape is worth an extra look before other modules copy it.

## Out of scope for this doc

Actually applying the fixes — inventory only.
