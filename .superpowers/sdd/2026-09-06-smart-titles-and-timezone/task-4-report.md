# Task 4 Report — Render resolved titles (list, view, export)

**Status:** complete. Commits: "feat: render resolved subject/causer titles on list,
view and export" + "fix: phpstan types and legacy getSubject tests for resolver
delegation".

**Changes:**

- `FbActivitiesTable`: `subject` column now computed via resolver (state +
  description = model label, gated by `subject.show_model_label`; sortable on
  `subject_type`; searchable on subject_type/subject_id). `causer` column computed
  via `resolveCauserTitle()` (keeps `-` null-causer search semantics + visibility
  permission).
- `FbActivityInfolist`: causer entry resolved; subject entry formats
  `infolist.subject_name` as `Label ↣ Title` (fa arrow variant from lang).
- `ActivityExporter`: new `subject` column (resolved title) alongside the now
  correctly-labeled `subject_type` column; causer via resolver. New lang key
  `table.subject_type` (en+fa).
- `FbActivity::getSubject()` deprecated, delegates to resolver, **no fresh query**;
  `getSubjectName()` deprecated (still functional).
- `FbActivityServiceTest`: legacy `getSubject` expectations updated to the new
  contract (non-Activity record → null; unknown class → "Missing Model ↣ Missing
  Model #42").

**Verify:** 50 tests / 130 assertions; phpstan clean (Activity instanceof narrows in
infolist closures; sort direction literal union); pint clean.

**Notes for Task 5:** subject column is now a computed-state column named `subject` —
adding `->url()` on it next; the fallback `Label #id` tests use regex `Podcast #\d+`.
