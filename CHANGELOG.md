# Changelog

All notable changes to `vm-engine/synmod-example` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - Unreleased

### Changed
- **Requires Laravel ^11.0|^12.0|^13.0** (added `^13.0` support) and `vm-engine/synapse` ^2.1|^3.0 — supports synapse's new default CSP-safe Alpine.js build.

### Fixed
- **CSP-safe Alpine.js compatibility.** `category-form`, `category-list`, and `example-list` migrated off inline `x-data="{ ... }"` object literals with methods and multi-statement `@click`/`x-on:*` expressions to the `Alpine.data()` registry pattern (required by `vm-engine/synapse` ^3.0's new default CSP-safe Alpine build), guarded against the `alpine:init`/`wire:navigate` timing race. `example-list`'s use of the `withBack()` global JS helper switched to the `$withBack()` Alpine magic, since bare globals aren't resolvable inside a CSP-restricted directive expression.
- `ExampleFactory`'s `@extends Factory<...>` PHPDoc referenced a nonexistent `App\Models\Model` placeholder type instead of the actual `Example` model — flagged by PHPStan level 5.

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
