# Known Issues — laravel-courier

_Last checked: 2026-08-02_

## Failing tests

No failing tests. `composer test:unit` (Pest, parallel) passes fully: 16 passed (47 assertions).

## Style / static-analysis debt

- `vendor/bin/pint --test` reports a clean pass — no style debt.
- `vendor/bin/rector --dry-run` reports **7 files** with pending refactors (mostly `AddOverrideAttributeToOverriddenMethodsRector` on facades/services, plus `ChangeOrIfContinueToMultiContinueRector` splitting combined `||` early-return conditions in `src/Services/RedxService.php`). Run `composer refacto` to apply. (This failure is what stops the chained `composer test` script before reaching lint/types/unit — each subsequent step was run individually to get the results below.)
- `vendor/bin/phpstan analyse` (level: `max`) reports **91 errors**, and `phpstan-baseline.neon` is present but **empty (0 entries)** — none of these are pre-accepted debt. The overwhelming majority are "no value type specified in iterable type array" on the courier gateway service classes (`src/Services/PathaoService.php`, `src/Services/RedxService.php`, `src/Services/SteadfastService.php`, `src/Services/SundarbanService.php`, `src/Services/RokomariService.php`) — these are third-party API wrapper methods (`get()`, `post()`, `headers()`, `track()`, `priceCalculator()`, `calculateCharge()`, `createParcel()`, etc.) typed as bare `array` without generics. A smaller set are real type-safety gaps: `Cannot cast mixed to string` (RedxService.php:137,187,201; SteadfastService.php:11; SundarbanService.php:11), a `Strict comparison using === between array<string, mixed> and false will always evaluate to false` at RedxService.php:171, and a `mixed` value passed where `PendingRequest::get()` expects `string` at RokomariService.php:13.

## TODO / FIXME markers

None found (`grep -rn "TODO\|FIXME" --include="*.php" src/ config/ database/ routes/` — no matches).

## Open GitHub issues

Not checked — the `gh` CLI is not installed in this environment.
