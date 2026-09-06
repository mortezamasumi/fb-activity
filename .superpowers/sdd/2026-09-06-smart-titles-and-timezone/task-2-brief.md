# Task 2 Brief — Wire timezone formatter into table, infolist, exporter, filter

**Files:**

- Modify: `src/Resources/Table/FbActivitiesTable.php`
- Modify: `src/Resources/Schemas/FbActivityInfolist.php`
- Modify: `src/Resources/Exports/ActivityExporter.php`

**Interfaces:** consumes `FbActivity::formatDateTime()` / `toStorageDate()`; replaces
`->jDateTime()` on created_at column/entry/exporter and `__jdatetime` in the property
heuristic; filter bounds wrapped with `toStorageDate()`.

**Key decision (from Task 1 report):** preserve current rendering byte-for-byte —
macro defaults (`fb-essentials date_format.time_simple` via `FbPersian::jDateTime`
null-format) — by calling `formatDateTime($state)` with no format.

**Steps:**

1. Table created_at: `->formatStateUsing(fn ($state) => FbActivity::formatDateTime($state))`
   (keep sortable).
2. Infolist created_at: same swap.
3. Exporter created_at: replace `->jDateTime()` with formatStateUsing using the same
   helper (`?? ''`).
4. Filter query bounds: wrap created_from/until with `toStorageDate()`.
5. Property-value date heuristic: `__jdatetime(null, Carbon::parse($v))` →
   `FbActivity::formatDateTime($v, 'Y/m/d')` (matches prior 'Y/m/d' intent — verify
   the current heuristic's format first).
6. Regression test: UTC-stored row displays shifted on the list page; filter across
   Tehran-day boundary finds the row.

**Verify:** pest new test file + ActivityResourceTest; analyse; pint.
