# agents.md

## Agent Guidance — laravel-package-courier

### Package Purpose
Template/scaffold for creating new Laravel packages in this monorepo. This directory should be copied and customized — it is not a functional package itself.

### When to Use This
When asked to create a new Laravel package in this monorepo:
1. Copy this directory to a new name (e.g., `laravel-newfeature`)
2. Follow the find-and-replace checklist in `CLAUDE.md`
3. Add the new directory as a git submodule in the parent repo
4. Update the parent `CLAUDE.md` package table

### Find-and-Replace Checklist When Creating a New Package
| Placeholder | Replace with |
|---|---|
| `Centrex` | `Centrex` |
| `Courier` | `YourPackageName` (PascalCase) |
| `courier` | `your-package-name` (kebab-case) |
| `package_description` | Actual one-line description |
| `This is my package courier` | Actual description in `composer.json` |
| `vendorname/courier` | `centrex/your-package-name` |

### Files to Rename After Copy
- `src/Courier.php` → `src/YourPackageName.php`
- `src/CourierServiceProvider.php` → `src/YourPackageNameServiceProvider.php`
- `src/Facades/Courier.php` → `src/Facades/YourPackageName.php`
- `config/courier.php` → `config/your-package-name.php`

### Files to Update After Rename
- `composer.json` — name, description, autoload namespace
- `src/YourPackageNameServiceProvider.php` — config key, migration path, view path
- `tests/TestCase.php` — service provider registration
- `workbench/` — update app config and providers

### Do Not
- Use this courier as-is in production — replace all placeholders first
- Add real logic to this directory — it's a template only
- Commit secrets or real API keys while scaffolding

### Verifying a New Package
After scaffolding, run from the new package directory:
```sh
composer install && composer test
```
All tests should pass on a fresh scaffold before adding real functionality.
