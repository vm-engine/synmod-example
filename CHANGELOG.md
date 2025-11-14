# Changelog

All notable changes to `vm-engine/synmod-example` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://repo.4visionmedia.com/vm-engine/synmod-example/compare/v1.0.0...HEAD
[1.0.0]: https://repo.4visionmedia.com/vm-engine/synmod-example/releases/tag/v1.0.0
