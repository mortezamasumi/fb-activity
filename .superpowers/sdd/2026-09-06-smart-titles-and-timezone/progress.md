# Progress — smart titles & timezone

## Preflight (2026-09-06)

- HEAD `ceb81ea` (main), working tree clean at start of implementation.
- Suite: `composer test` (Pest, tests/Tests), static: `composer analyse` (phpstan).
- PHP `^8.5`, Filament 5, spatie/laravel-activitylog ^4, fb-essentials installed.
- Fixture models: `tests/Services/Podcast.php` (LogsActivity, logAll, attr `text`),
  `tests/Services/User.php`; panel registered in `tests/TestCase.php`.
- Config current keys: navigation/export/include_logs/exclude_logs — must stay.
- Existing behavior to preserve: `jDateTime()` macros on created_at (defaults from
  `fb-activity.table.date_format` per column config? — verified: table macro default
  format comes from `fb-essentials::fb-essentials.date_format.time_simple`; the
  `fb-activity.table.date_format` key exists but is NOT currently referenced by the
  column — Task 2 will keep macro-default behavior unless plan says otherwise).
- Baseline suite run: pending (will run after Task 1 files land, before commit).

## Task state

- [x] Task 1 — config surface + formatDateTime/toStorageDate (commit b0c5a1c~1)
- [x] Task 2 — wire formatter into table/infolist/exporter/filter (b0c5a1c)
- [x] Task 3 — HasActivityTitle + resolver (185b976)
- [x] Task 4 — render resolved titles (8a6a9f3 + ff718b8)
- [x] Task 5 — subject links (6893d71 + b64f6fc)
- [x] Task 6 — badges/icons/filters/README (58e6b02)
- [x] Task 7 — old/new diff table (a89f248) — APPROVED, done

## Final state (2026-09-06)

HEAD a89f248, main. Suite: 61 tests / 155 assertions passing. phpstan + pint clean.
7 conventional commits. All SDD task briefs + reports in this ledger.

Key implementation discoveries (for future plans):

- FbActivity + resolver must be SINGLETONS for per-request memoization (provider now
  binds both).
- Filament Resource::getModelLabel() default is kebab-LOWERCASE ("podcast") — apps
  wanting title-case fallbacks set subject.labels per model.
- assertTableColumnStateSet asserts raw state; formatted output needs
  assertTableColumnFormattedStateSet.
- The fb-essentials jDateTime macro default format is time_full ('l j F Y H:i'),
  not time_simple.
- getExtraProperty() throws when properties is null — always guard.
- Filament url()/color() closures must be null-safe per record (a column-level null
  disables link/badge for that row).
