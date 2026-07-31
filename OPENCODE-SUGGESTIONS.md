# OPENCODE-SUGGESTIONS

Status: 15 tests passing (72 assertions) — 0 items pending, 18 items fixed.

Review carried out while bringing fb-activity up to the fb-* package standard
(Pint + PHPStan level 8, CI six gates, README/docs). Findings are recorded
below; implemented items are struck through with a note. Applied as one
approved batch commit.

## Bugs

1. ~~`.github/workflows/ci.yml:48-49` — the Pest test step is commented out, so CI
   never executes the test suite (the `release` job's `needs: test` is the only
   gate that can ever pass). Re-enable it and add the missing gates
   (validate/audit/pint/phpstan) — see item 11.~~
   **FIXED** — test step re-enabled; full six-gate CI with `prefer-lowest` and
   `checkout@v5` added (item 11).
2. ~~`src/Resources/Table/FbActivitiesTable.php:81` — the `created_at` filter passes
   the raw translation key to `->label()` without `__()`, so the filter label is
   rendered as the literal string `fb-activity::fb-activity.table.created_at`.
   Wrap it in `__()` like every other label in the package.~~
   **FIXED** — wrapped in `__()`.
3. ~~`src/Resources/Table/FbActivitiesTable.php:73,127` — `Auth::user()->can(...)`
   is called on a possibly-null `Auth::user()` (list page uses `?->can()` at
   `ListActivity.php:23`, but the table does not). Use `Auth::user()?->can(...)`.~~
   **FIXED** — `(bool) Auth::user()?->can(...)` in both places.
4. ~~`src/FbActivity.php:26-30` — `getSubject()` reads `$record->subject_id` and
   `$subjectModel?->name ?? $subjectModel?->title ?? $subjectModel?->text` via
   magic property access on `Model` (PHPStan level 8: undefined property). Follow
   the fb-auth precedent: `getAttribute()` / `getKey()` with explicit guards.~~
   **FIXED** — `getAttribute('subject_id')`, `is_subclass_of($state, Model::class)`
   guard, `whereKey(...)->first()`, `getAttribute('name'/'title'/'text')`; covered
   by `tests/Tests/FbActivityServiceTest.php`.
5. ~~`src/Resources/Schemas/FbActivityInfolist.php:74,79,92-93` — `$record` is typed
   `?Model` but is dereferenced without null-safety and via magic properties
   (`$record->log_name`, `$record->properties`). Lines 74 and 79 are inconsistent
   (`->` vs `?->`). PHPStan level 8 will flag undefined property + possibly-null.~~
   **FIXED** — `getAttribute()` + null-safe record handling; the properties
   section now uses a typed `Collection` with null guards and typed closures.
6. ~~`src/Facades/FbActivity.php:8-9` — `@method` annotations use unqualified `?Model`,
   which resolves to the facade namespace (`class.notFound`). Fully qualify as
   `\Illuminate\Database\Eloquent\Model` (same fix as fb-auth item 6/12).~~
   **FIXED** — fully qualified in both `@method` lines.
7. ~~`src/Testing/TestsFbActivity.php:8` — `@mixin Testable` on a generic class
   (`missingType.generics`). Use `@mixin \Livewire\Features\SupportTesting\Testable<\Livewire\Component>`
   (same fix as fb-auth item 15).~~
   **FIXED** — parameterized `@mixin`.
8. ~~`database/migrations/create_activity_log_table.php:8,24` — anonymous class
   migration; `up()` / `down()` lack return types (`missingType.return` at level 8).~~
   **FIXED** — `: void` on both methods.
9. ~~`composer audit` — guzzlehttp/guzzle 7.14.0 flagged by 4 advisories (requires
   >= 7.15.1); it is pulled through the production tree (filament →
   spatie/laravel-google-fonts). `composer update guzzlehttp/guzzle
   guzzlehttp/psr7 --with-all-dependencies` fixes the local lock; CI also needs
   the audit gate (item 11) to enforce it.~~
   **FIXED** — updated to guzzle 7.15.2, `composer audit` clean; audit gate added
   to CI.
10. ~~`src/FbActivity.php:33,40` — `getSubject()` calls `getSubjectName()` twice;
    minor, but the value is reused below the guard.~~
    **FIXED** — single `$sn` computation reused in the translation payload.

## API cleanliness / typos

11. ~~`.github/workflows/ci.yml` — stale boilerplate: pest test step commented out,
    no validate/audit/pint/phpstan gates, `stability: [prefer-stable]` only,
    `actions/checkout@v4`. Rewrite to match fb-essentials/fb-sms (six gates +
    `prefer-lowest` + `checkout@v5`).~~
    **FIXED** — identical to fb-essentials CI (six gates + `prefer-lowest` +
    `checkout@v5`).
12. ~~`src/Resources/Table/FbActivitiesTable.php:43-48` — commented-out code
    (`getSubjectName`/`tooltip`/`copyable`/`copyableState`) left in the column
    definition. Remove dead code.~~
    **FIXED** — removed.
13. ~~`tests/Tests/ActivityResourceTest.php:51-55,106` and
    `phpunit.xml.dist:20` — commented-out `beforeEach`, an unused
    `@disregard`-style assertion, and a commented `AUTH_MODEL` env pointing at a
    non-existent `Tests\Models\User` path (real one is `Tests\Services\User`).
    Remove/clean dead comments.~~
    **FIXED** — dead `beforeEach` and commented assertion removed;
    `AUTH_MODEL` now points at `\Mortezamasumi\FbActivity\Tests\Services\User`.

## Meta / release-readiness

14. ~~`composer.json` — boilerplate `description` ("This is my package fb-activity"),
    `keywords` missing `filament`, stale `Mortezamasumi\FbActivity\Database\Factories\`
    autoload pointing at a non-existent `database/factories/` (package only ships
    `database/migrations`), missing `pint`/`analyse` scripts, missing
    `laravel/pint`, `phpstan/phpstan`, `larastan/larastan` dev-deps, and
    `phpstan/extension-installer` in allow-plugins. Bring to fb-auth/essentials
    shape (allow-plugins only `pestphp/pest-plugin`).~~
    **FIXED** — professional description/keywords, autoload trimmed, `pint`/
    `analyse` scripts + pint/phpstan/larastan dev-deps added, allow-plugins only
    `pestphp/pest-plugin`; `composer validate --strict` passes.
15. ~~`README.md` — full boilerplate rewrite needed: one-line tagline + features,
    real badges (current ones point at non-existent `run-tests.yml` and
    `fix-php-code-style-issues.yml` workflows), real install/publish tags
    (`fb-activity-config`, `fb-activity-migrations` — the claimed
    `fb-activity-views` tag does not exist, the provider has no `hasViews()`),
    real published-config contents (not the empty `return [];`), plugin usage,
    `FbActivity::getSubjectName()`/`getSubject()` reference, config keys incl.
    `ACTIVITY_EXCLUDE_LOGS` / `ACTIVITY_INCLUDE_LOGS` / `ACTIVITY_MAX_EXPORT_ROWS`,
    support-policy table, links to `.github/CONTRIBUTING.md` + `.github/SECURITY.md`.~~
    **FIXED** — full rewrite with real badges, features, publish tags, config
    reference, plugin usage, permissions table, support policy, and doc links.
16. ~~`.github/` — missing `CONTRIBUTING.md` and `SECURITY.md` (only `workflows/`).
    Add canonical copies identical to fb-essentials/fb-auth.~~
    **FIXED** — canonical copies added.
17. ~~`CHANGELOG.md` — placeholder `## 1.0.0 - 202X-XX-XX`. Replace with real dated
    entries derived from git history.~~
    **FIXED** — real dated entries from tags (1.0.0 → 5.0.3).
18. ~~Missing `pint.json` (`{"preset": "laravel"}`) and `phpstan.neon.dist`
    (level 8, larastan include, `paths: [src]`, `tmpDir: build/phpstan`,
    documented `ignoreErrors` for the Filament `Testable` mixin / macro magic if
    needed). `.gitignore` already covers `phpstan.neon`.~~
    **FIXED** — both added; `ignoreErrors` documents the runtime `jDateTime()`
    macro (fb-essentials) and the `__fb_setting()` helper (fb-setting).

## Tests

- Current suite: 15 tests / 72 assertions green (`composer test`).
- ~~Test titles: mostly professional already; reword the informal ones to match
  AGENTS.md expectations, e.g. `can show empty table` → `can render an empty
  activity table`, `can view by url` → `can render the view page for a record by
  its URL`, `can view data` → `can render the activity details on the view page`.~~
  **FIXED** — titles professionalized; guest tests now read "cannot access the
  resource when unauthenticated" / "without permission".
- ~~Missing coverage (add after fixes): `FbActivity::getSubjectName()` /
  `getSubject()` branches — empty state, non-class `$state` fallback,
  class-without-name/title/text subject, subject found vs not found; the
  `created_at` filter label translation; null-safe `Auth` behaviour already
  exercised by the unauthorized-user tests.~~
  **FIXED** — `tests/Tests/FbActivityServiceTest.php` covers empty subject type,
  humanized class name, empty state, name-attribute resolution, unknown-class
  fallback to subject id, and null-name path.
- ~~After fixes: run `composer pint` and `composer analyse` and leave both clean
  (PHPStan needs larastan + config first — item 14).~~
  **FIXED** — Pint clean, PHPStan level 8 clean (0 errors), `composer audit`
  clean, `composer validate --strict` passes.
