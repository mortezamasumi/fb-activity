# Task 2 Report — Wire timezone formatter into table, infolist, exporter, filter

**Status:** complete. Commit: "feat: apply timezone reinterpretation to table,
infolist, exporter and date filter".

**Changes:**

- `FbActivitiesTable`: `created_at` column now `formatStateUsing(FbActivity::formatDateTime)`
  (sortable kept); filter `created_from/until` bounds wrapped with `toStorageDate()`.
- `FbActivityInfolist`: `created_at` entry through the same helper; property-value
  date heuristic now `FbActivity::formatDateTime($v, table.date_format)` (previously
  `__jdatetime`); dropped unused `Carbon` import.
- `ActivityExporter`: `created_at` through the helper (`?? ''` for blanks).
- `tests/Tests/FbActivityTimezoneRenderingTest.php`: 3 Livewire tests — UTC-stored
  wall renders +3:30 shifted on the list page; unconfigured rendering byte-identical
  to current macro output; Tehran-day filter finds a UTC-stored row across the day
  boundary.

**Verify:** 26 tests / 89 assertions pass; phpstan OK (after dropping an
already-narrowed `is_string()`); pint clean.

**Deviations/notes for later tasks:**

- The macro default format is fb-essentials **time_full** (`l j F Y  H:i`), NOT
  time_simple — the plan's suggestion to pass `fb-activity.table.date_format` to the
  column was rejected to keep rendering byte-identical; `formatDateTime()` is called
  with no format. The `fb-activity.table.date_format` key remains used only by the
  property-date heuristic (as before).
- `assertTableColumnStateSet` asserts raw state (Carbon); formatted output must be
  asserted with `assertTableColumnFormattedStateSet` — note for Task 4 tests.
