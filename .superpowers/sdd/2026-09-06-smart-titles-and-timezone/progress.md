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

- [in-progress] Task 1 — config surface + formatDateTime/toStorageDate
- [ ] Task 2 — wire formatter into table/infolist/exporter/filter
- [ ] Task 3 — HasActivityTitle + resolver
- [ ] Task 4 — render resolved titles
- [ ] Task 5 — subject links
- [ ] Task 6 — badges/icons/filters/README
- [ ] Task 7 — old/new diff table (approved)
