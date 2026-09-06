# Task 1 Brief — Config surface + timezone formatter helper

**Files:**

- Modify: `config/fb-activity.php`
- Modify: `src/FbActivity.php`
- Test (create): `tests/Tests/FbActivityTimezoneTest.php`

**Interfaces:**

- `FbActivity::formatDateTime(mixed $value, ?string $format = null): ?string`
- `FbActivity::toStorageDate(string $date): string`
- Config keys added: `timezone.{storage,display}`, `subject.*`, `causer.*`,
  `events.{colors,icons}`, `logs.colors` (per spec "Config surface"). Existing keys
  byte-identical.

**Steps:**

1. Add config keys.
2. Implement `formatDateTime()`: null storage tz → `FbPersian::jDateTime($format,
$value)` (current behavior). Else parse raw (`Y-m-d H:i:s` createFromFormat, then
   Carbon::parse fallback) in storage tz → setTimezone(display ?? app) → render via
   `FbPersian::jDateTime($format, $converted, $displayTz)`.
3. Implement `toStorageDate()`: identity when storage/display null; else
   `Carbon::parse($date, display ?? app)->setTimezone(storage)->toDateString()`.
4. Pest tests: baseline equality, UTC→Tehran shift on `H:i` tail, toStorageDate
   round-trip, blank safety.

**Verify:** run `vendor/bin/pest tests/Tests/FbActivityTimezoneTest.php`, then
`composer analyse`. Pint before commit.
