# Task 6 Report — Badges, icons, filters, README

**Status:** complete. Commit: "feat: configurable badge colors/icons, log and event
filters, README docs".

**Changes:**

- `log_name` badge color from `fb-activity.logs.colors` (null → default styling);
  `event` badge icon from `events.icons` and color from `events.colors` with legacy
  `draft => gray` + `primary` fallback preserved (created/updated/deleted/restored
  defaults now live in the published config).
- New `SelectFilter`s: `log_name` (distinct DB values, ucwords), `event`
  (configured keys ∪ distinct DB values, deduped, ucwords). Both bilingual labels via
  existing table lang keys.
- README: full config surface documented — smart titles pipeline (6 steps with
  `HasActivityTitle` example), subject links (urls map forms + link.enabled +
  access-policy note), timezone reinterpretation (env vars, no-migration rationale,
  filter-bound conversion), badge/filter config, and the search-on-subject_type
  caveat + labels override for kebab-case casing.

**Verify:** 57 tests / 137 assertions; phpstan clean (collect() template fix); pint
clean.
