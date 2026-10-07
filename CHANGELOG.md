# Changelog

All notable changes to `vm-engine/synmod-example` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - Unreleased

### Added
- **Pattern Catalog** (`/example/catalog`, first menu item, `example.any`): registry-driven index of every list/form/page/component/integration pattern the module demonstrates or will (`src/Catalog/PatternCatalog.php`, 57 entries, built vs planned), card grid with search, group chips, built-only toggle, progress bar and `x-synapse-copy-button` source paths. Guard tests keep built entries pointing at real routes and files.
- Showcase schema via additive migrations: `examples.slug` (unique, backfilled for existing rows), `status` (`ExampleStatus` enum: draft/review/published with label + synapse color), `due_at`, `content`, `meta` (JSON), `position`, `created_by`/`updated_by` (`HasCreator`/`HasUpdater`), soft deletes.
- `ExampleTag` (many-to-many), `ExampleNode` (self-referencing tree), `ExampleAttachment` (files deleted with the row and on example force delete) with factories; `ExampleFactory` states `draft()`/`review()`/`published()`/`dueThisMonth()`/`withTags()`.
- Seeders: 8 tags, 60 examples (even status spread, 20 due this month, 1-3 tags each), 30-node three-level tree.
- **List patterns** (sub-project 2, all reachable from the Pattern Catalog, 18/57 built): card grid with status chips, bulk select + bulk status/delete with "select all matching", trash & restore with delete-forever, drag-sortable rows (`wire:sort`), grouped table, expandable rows, load more / infinite scroll (`wire:intersect`), inline filter row (URL state) and Excel export (sync download + queued export with progress) sharing one serializable `ExampleExportQuery`.
- **Tags** screen (inline edit with `x-synapse-color-picker`, add row, delete) and **Nodes** screen (tree with drag reorder across parents via `wire:sort` groups, inline add/rename, branch delete); new `example.tag` / `example.node` permissions and menu items.
- **Form patterns** (sub-project 3, 33/57 built): full **Example editor** (`/example/editor/{id?}`) with a sticky action bar + linked error summary, slug auto-fill, real-time validation, rich text, key/value meta repeater with a raw-JSON toggle, remote + creatable tags select, cover + multi-file attachments, conditional fields and color picker; layout pages for a modal hosting a child form, drawer create/edit, tabbed form with per-tab error counts, modal wizard and page wizard (`x-synapse-steps`, per-step validation).
- `<x-example::rich-text>`: local Jodit wrapper (lazy `import('jodit')`, `wire:ignore`), HTML purified server-side by `RichTextSanitizer`; `TagResolver` turns typed tag names into tags.
- `php artisan example:setup` adds the module JS import to the host `resources/js/app.js` (idempotent).
- **Page patterns** (sub-project 4, 44/57 built): dashboard (stat tiles, ApexCharts donut + line, lazy panels), status report (status × category matrix, bar chart updated in place, print view on a bare layout, queued Excel export), live export progress page (`wire:poll`, owner-only, download), detail page with Overview / Attachments / Related tabs (lazy children, lightbox, confirm delete), kanban board (drag between status columns, publish-note gate, WIP limit badge), calendar (month view of due dates, day quick-create), split-pane node browser, empty states and a component gallery.
- **Settings** page (`/example/settings`, new `example.settings` permission and menu item): rows per page, due column, default status, max attachments and kanban WIP limit, stored in DbConfig by `ExampleSettings` and read by the list, inline filters, editor, drawer form and kanban.
- `<x-example::chart>`: ApexCharts wrapper (lazy `import('apexcharts')`, `wire:ignore`, updated via `example-chart-{id}` events, dark-mode aware, empty state); `ExampleStats` aggregates; shared `empty-state` partial (also used by the example list, with "Clear filters").
- **Integration patterns** (sub-project 5, catalog complete at 57/57), all under the catalog's Integrations group:
  - Auth directives + helpers page with live results and a guarded-action demo; every Livewire action in the module now checks permissions through `GuardsBackendPermission::guardAction()` (a guard test blocks hand-rolled checks).
  - Activity log: an `ExampleObserver` (`#[ObservedBy]`) records every example write with whitelisted before/after snapshots (never content); bulk actions and Empty trash log one summary. New **Example activity** page with the OTP-gated metadata viewer.
  - **Empty trash** on the trash list, protected by a one-time password (single-use, purpose-bound token).
  - **Example author** role (seeder + `example:setup`): update/delete only your own examples via an ABAC condition; Conditional permission demo page.
  - User field extension: `users.example_default_category_id` (migration) on the backend user form via `SynAuthExtension`, preselected in the editor and calendar quick-create.
  - Remembered filters (`RemembersQueryParams`) on the example list and inline filters.
  - **API v1** (`routes/api.v1.php` → `/api/v1/example/*`): list/show/categories/status, API resources and FormRequests; HMAC-signed `POST /examples` (`api.signed`); API page listing the endpoints; Postman collection in `docs/postman/` (login + self-signing create).
  - Notifications: publishing notifies admins (except the actor; one summary for bulk), a finished import notifies the importer; test-notification page.
  - Global search: examples and three shortcuts in the Ctrl/⌘+K palette (`module.json` `searchables`), kept in sync by the observer; `example:search-reindex` command.
  - **Excel import** page: template download, xlsx/csv upload, queued per-row validation with a skipped-rows report and live progress.
  - Public **frontend pages** (`/example`): list with category filter, detail by slug and search with highlighting — published examples only.

### Changed
- **Requires `vm-engine/synapse` ^3.2 and `vm-engine/synapps-auth` ^3.0**, Laravel ^11.0|^12.0|^13.0.
- `example-list` / `category-list` on synapse conventions: `WithSortablePagination` (whitelisted sort fields), `x-synapse-panel`, `-search-box` (filter drawer in the trailing slot), `-sort-icon`, `-per-page-selector`; status badge column; drawer titles computed in the class (no `@php` in views).
- Deleting an example is now a soft delete; its uploaded file is removed only on force delete.
- Backend menu and form icons switched to Phosphor.
- `declare(strict_types=1)` on every PHP file.
- PHPStan level 7 with no ignore list (now covering `database/` too): typed `rules()`, form array properties, `?TemporaryUploadedFile $file`, `newFactory()` returns, `HasFactory` generics, scope builders and relation generics. `/build/` added to `.gitignore`.
- Requires `mews/purifier` ^3.4; npm `jodit` ^4.15 and `apexcharts` ^7.8.
- Export columns live on `ExampleExportQuery::columns()` (shared by the list and report exports); attachment delete moved to a `DeletesAttachments` trait; the attachment limit comes from settings.
- Example titles in the list link to the detail page.
- All date inputs use `<x-synapse-datepicker>` (filter row uses `wire:model.live`); a guard test fails on native `type="date"` inputs.
- `Example` defaults the NOT NULL legacy columns (`protected`, `number`, `dropdown`) so the new forms can create rows; `ExampleAttachment` uses `WithDeleteToken`.

- Requires `vm-engine/synapse` 3.3 (`ReceivesImportTask`) and `vm-engine/synapps-auth` 3.0.1 (extension fields, OTP modal); bump the composer constraints at release.
- The catalog links a `frontend:<route>` entry to a public page.

### Fixed
- **CSP-safe Alpine.js compatibility.** `category-form`, `category-list`, and `example-list` migrated off inline `x-data="{ ... }"` object literals with methods and multi-statement `@click`/`x-on:*` expressions to the `Alpine.data()` registry pattern (required by `vm-engine/synapse` ^3.0's new default CSP-safe Alpine build), guarded against the `alpine:init`/`wire:navigate` timing race. `example-list`'s use of the `withBack()` global JS helper switched to the `$withBack()` Alpine magic, since bare globals aren't resolvable inside a CSP-restricted directive expression.
- `ExampleFactory`'s `@extends Factory<...>` PHPDoc referenced a nonexistent `App\Models\Model` placeholder type instead of the actual `Example` model — flagged by PHPStan level 5.
- Migrated the remaining `<x-synapse-select>`/`<x-synapse-multiselect>` usages (removed from `vm-engine/synapse` v3.0) to `<x-synapse-adv-select>` in `category-list`, `example-form`, and `example-list` — these views were throwing unknown-component errors. `statusOptions()`/`options()` reshaped to the value/label array format the new component requires.
- Tag slugs are now unique (`-2`, `-3`…) when two names slugify the same.
- Real-time fields use `wire:model.live.blur` (Livewire 4.1's `.blur` alone no longer sends a request).
- `category-list` had no page `<title>`; factories no longer pass `array|string` faker values to `ucfirst()`/`Str::slug()`.
- Confirm dialogs stayed open after Confirm: every confirmed action (example, category, tag, node, trash, bulk and attachment deletes) now dispatches `synapse-confirmed`.

### Removed
- Unused `example-page` / `button-sample` components (Bootstrap markup, CSP-blocked `alert()`), and the completed `docs/STRICT_TYPES_PLAN.md`.

### Documentation
- `CLAUDE.md`: added the `composer.json` local-dev `version` key policy and git commit/push approval policy.

## [2.0.2] - 2026-02-24

### Fixed
- **Page titles**: Added `render()` method to `example-list` and `example-form` full-page SFC components to correctly set the browser `<title>` tag via `$this->view()->title(page_title($this->title()))`

### Changed
- **PHPStan config**: Completed `phpstan.neon` with `includes`, `level`, and `paths` so `--configuration` flag works correctly

## [2.0.1] - 2026-02-20

### Fixed
- Fixed pagination bug: deleting the last item on a page now correctly loads the previous page content (computed cache invalidation after `setPage()`)
- Added missing pagination adjustment to category-list delete method

### Changed
- Updated all Livewire component references from dot notation to namespace notation (`example.component` → `example::component`) for Synapse v2.2.0 compatibility
  - Routes in `web.backend.php`
  - Livewire tags in Blade views (`category-list`, `example-page`)
  - Event dispatch target in `category-form`
  - All Livewire test references in `ExampleTest.php`
- Updated CLAUDE.md to reflect auto-registration via `AutoRegistersComponents` trait

## [2.0.0] - 2026-02-20

### Changed
- Migrated to Volt single-file components
- List state preservation improvements
- UX improvements

## [1.0.3] - 2025-12-26

### Added
- Added page titles to all Livewire components using `title()` method
  - `ExampleList`: "Example List"
  - `ExampleForm`: Dynamic titles - "Add Example Form" or "Edit Example Form"
  - `CategoryList`: "Category List"
  - `ExamplePage`: "Example"

### Changed
- Enhanced SEO and browser tab identification with descriptive page titles

## [1.0.2] - 2025-11-29

### Fixed
- Fixed route helper usage: changed from `route()` to `backend_route()` helper for all backend routes
- Fixed test assertion syntax in `ExampleTest.php` for delete operation
- Improved route consistency across views and components

### Changed
- Updated Claude Code settings to allow git tag and push operations

## [1.0.1] - 2025-01-14

### Security
- Enhanced security measures and bug fixes

## [1.0.0] - 2025-01-14

### Added
- Initial release of Example Module
- CRUD operations for examples with comprehensive form validation
- Category management system
- Advanced filtering capabilities:
  - Search by text, textarea, and email fields
  - Filter by dropdown options
  - Filter by single or multiple categories
  - Combine multiple filters
- Sorting functionality (ascending/descending)
- File upload support (PDF, DOC, XLS, PPT formats)
- Comprehensive test suite with 24 tests covering:
  - Create, update, delete operations
  - Validation rules
  - List filtering and sorting
  - ACL integration
- Livewire v3 components with Form Objects pattern
- Hierarchical ACL permission structure
- Backend navigation with multi-level menus
- Model factories and seeders
- Full documentation and examples

### Features
- **Example Management**: Complete CRUD with validation
- **Category System**: Organize examples by categories
- **Advanced Search**: Multi-field search with minimum character requirement
- **Flexible Filtering**: Dropdown and category-based filters
- **Permission Controls**: Fine-grained ACL integration
- **Form Validation**: Email, password complexity, required fields
- **File Management**: Upload and deletion of document files

[Unreleased]: https://repo.4visionmedia.com/vm-engine/synmod-example/compare/v2.0.1...HEAD
[2.0.1]: https://repo.4visionmedia.com/vm-engine/synmod-example/compare/v2.0.0...v2.0.1
[2.0.0]: https://repo.4visionmedia.com/vm-engine/synmod-example/compare/v1.0.3...v2.0.0
[1.0.3]: https://repo.4visionmedia.com/vm-engine/synmod-example/compare/v1.0.2...v1.0.3
[1.0.2]: https://repo.4visionmedia.com/vm-engine/synmod-example/compare/v1.0.1...v1.0.2
[1.0.1]: https://repo.4visionmedia.com/vm-engine/synmod-example/compare/v1.0.0...v1.0.1
[1.0.0]: https://repo.4visionmedia.com/vm-engine/synmod-example/releases/tag/v1.0.0
