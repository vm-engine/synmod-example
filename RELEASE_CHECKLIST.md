# Release Checklist for vm-engine/synmod-example

Use this checklist before releasing a new version of the package.

## ✅ Pre-Release Tasks (COMPLETED)

### Files Created
- [x] `.gitignore` - Excludes vendor, composer.lock, and development files
- [x] `LICENSE` - MIT License
- [x] `CHANGELOG.md` - Version history and changes
- [x] Enhanced `composer.json` with:
  - Keywords for discoverability
  - License information
  - PHP and Laravel version constraints
  - Service provider auto-discovery

### Code Quality
- [x] All tests passing (24 tests, 62 assertions)
- [x] Module.json properly configured
- [x] Namespaces correctly set (VmEngine\Example\)
- [x] Service provider registered

## ⚠️ Actions Required Before Release

### 1. Clean Up Development Files
```bash
# Remove vendor directory (should NOT be in git)
rm -rf vendor/

# Remove composer.lock (not needed for libraries)
rm composer.lock

# Verify .gitignore is working
git status
```

### 2. Update Version Numbers
- [x] Update version in `module.json` (currently: 1.0.0)
- [x] Update version in `CHANGELOG.md`
- [x] Decided on semantic version: 1.0.0 (Major release)

### 3. Review Documentation
- [ ] README.md is accurate and complete
- [ ] All installation instructions are correct
- [ ] Test commands work as documented
- [ ] Screenshots/examples are up to date (if any)

### 4. Git Repository Preparation
```bash
# Add all new files
git add .gitignore LICENSE CHANGELOG.md

# Check what will be committed
git status

# Commit the changes
git add -A
git commit -m "Prepare for v1.0.0 release"

# Create version tag
git tag -a v1.0.0 -m "Release version 1.0.0"
```

### 5. Final Testing
- [ ] Run all tests: `./vendor/bin/pest vendor/vm-engine/synmod-example/tests`
- [ ] Check code style: `./vendor/bin/pint vendor/vm-engine/synmod-example`
- [ ] Test installation in a fresh Laravel project
- [ ] Verify migrations run successfully
- [ ] Check all routes are accessible

### 6. Composer Package Registration
- [ ] Update composer.json repositories section if publishing to Packagist
- [ ] Remove or update `repositories` section for public release
- [ ] Ensure dependencies are publicly available

### 7. Documentation Review
- [ ] CLAUDE.md is up to date (for AI assistants)
- [ ] API documentation is complete (if any)
- [ ] Examples are working
- [ ] Links in README are valid

## 📦 Release Process

### For Internal/Private Repository
```bash
# Push to repository
git push origin master
git push origin v1.0.0

# Update dependent projects
composer update vm-engine/synmod-example
```

### For Public Release (Packagist)
1. **Clean composer.json**:
   - Remove local `repositories` section
   - Ensure all dependencies are on Packagist
   - Update stability to `"minimum-stability": "stable"`

2. **Submit to Packagist**:
   - Create account at https://packagist.org
   - Submit package URL
   - Configure GitHub webhook for auto-updates

3. **Tag the release**:
   ```bash
   git tag -a v1.0.0 -m "Initial release"
   git push origin v1.0.0
   ```

## 🔍 Post-Release Verification

- [ ] Package installs correctly: `composer require vm-engine/synmod-example`
- [ ] Service provider auto-discovered
- [ ] Migrations run: `php artisan migrate`
- [ ] Tests pass in consumer project
- [ ] Routes are registered correctly
- [ ] ACL permissions work

## 📋 Release Notes Template

```markdown
## What's New in v1.0.0

### Features
- Complete CRUD operations for examples
- Advanced filtering and search functionality
- Category management system
- Comprehensive test suite (24 tests)
- ACL integration with hierarchical permissions

### Requirements
- PHP ^8.2
- Laravel ^11.0|^12.0
- Livewire ^3.0
- vm-engine/synapse
- vm-engine/synapps-auth

### Installation
composer require vm-engine/synmod-example
php artisan migrate
```

## ⚡ Quick Commands

```bash
# Clean vendor
rm -rf vendor/ composer.lock

# Run tests
cd ../../ && ./vendor/bin/pest vendor/vm-engine/synmod-example/tests

# Fix code style
cd ../../ && ./vendor/bin/pint vendor/vm-engine/synmod-example

# Create release
git add -A
git commit -m "Release v1.0.0"
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin master --tags
```

## 🚨 Critical Checks

- [ ] **NO** vendor directory in git
- [ ] **NO** .env files
- [ ] **NO** hardcoded credentials
- [ ] **NO** development-only code
- [ ] License file present
- [ ] All tests passing
- [ ] Documentation complete

---

**Current Status**: ✅ Ready for release

**Next Version**: 1.0.0 → 1.1.0 (for next feature) or 1.0.1 (for bugfix)
