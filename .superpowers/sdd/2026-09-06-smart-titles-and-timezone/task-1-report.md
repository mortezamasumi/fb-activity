# Task 1 Report — Config surface + timezone formatter helper

**Status:** complete. Commit: "feat: configurable storage/display timezone
reinterpretation helper".

**Changes:**

- `config/fb-activity.php`: added `timezone.{storage,display}` (env-wired), plus
  `subject/causer/events/logs` config surface from the spec (used by later tasks).
  Existing keys untouched.
- `src/FbActivity.php`: added `formatDateTime()` and `toStorageDate()` per spec D4.
  `createFromFormat('Y-m-d H:i:s')` → `Carbon::parse` fallback; unparseable input →
  null (never throws); blank → ''. Renders via `FbPersian::jDateTime` with explicit
  display tz so the stringified wall time is reinterpreted in the same zone it was
  converted to (immune to the fb-essentials stringify quirk).
- `tests/Tests/FbActivityTimezoneTest.php`: 8 tests — baseline equality with the
  macro (null config), UTC→Tehran +3:30 shift, display-null fallback to app tz,
  toStorageDate round-trip (Tehran→UTC day boundary), identity when unconfigured,
  blank safety, unparseable→null, Carbon instance input.

**Verify:** 8/8 new tests pass; full suite 23 passed (81 assertions); phpstan OK;
pint clean.

**Deviation from plan:** none. Note for Task 2: the table macro's default format when
unspecified is `fb-essentials date_format.time_simple`; the `fb-activity.table.date_format`
lang key exists but is currently unused by the column — Task 2 will preserve current
rendering byte-for-byte (macro defaults), not adopt the unused key, unless trivially
equivalent.
