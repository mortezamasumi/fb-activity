# Task 7 Report — Old/new changes diff table on the view page

**Status:** complete. Commit: "feat: old/new changes diff table on the activity view
page".

**Changes:**

- `FbActivityInfolist`: new dedicated **Changes** section — visible only when
  `properties` holds both `old` and `attributes` (both non-empty). One 3-column grid
  row per changed attribute: humanized attribute name | old value (danger) | new value
  (success). Values stringified through the shared `stringify()` helper (strings kept,
  bools 1/0, arrays/objects JSON-encoded, date-ish strings via `formatDateTime`) —
  same semantics as the flat list, now de-duplicated.
- When the diff renders, `old`/`attributes` are excluded from the flat KeyValue
  properties section; custom property keys keep the flat layout (regression-tested).
- Lang keys (en+fa): `infolist.changes`, `.attribute`, `.old`, `.new`.

**Verify:** 61 tests / 155 assertions (4 new diff tests incl. spatie-update round-trip
via the Podcast fixture); phpstan clean; pint clean.

**Implementation notes:**

- `changesSection(?Model $record)` was initially called with a nonexistent variable in
  the static `configure()` context; restructured to `Section->visible(fn) +
->schema(fn)` closures so the record is injected by Filament at render time.
- The humanizer headlines snake_case attribute names ("first_name" → "First Name");
  model-specific label lookup via lang files is a future enhancement, not in the plan.

---

# Plan complete — all 7 tasks done

- Tests: 61 passing (155 assertions) — from 15 at start of implementation.
- phpstan level: clean. Pint: clean.
- Conventional commits used throughout (semantic-release compatible).
