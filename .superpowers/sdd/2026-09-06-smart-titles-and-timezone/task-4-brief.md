# Task 4 Brief — Render resolved titles (list column, view page, exporter)

**Files:**

- `src/Resources/Table/FbActivitiesTable.php` — subject column: state via
  `FbActivity::resolveSubjectTitle($record)` with old raw `subject_type` as
  fallback; `->description()` model label when `subject.show_model_label`;
  searchable on subject_type/subject_id; sortable by subject_type. Causer column:
  computed state via `resolveCauserTitle()`, keep `-` search semantics.
- `src/Resources/Schemas/FbActivityInfolist.php` — subject entry formats
  `subject_name` lang key with a=model label, b=resolved title. Causer entry:
  resolved title.
- `src/Resources/Exports/ActivityExporter.php` — subject column resolved title;
  causer via `resolveCauserTitle()`.
- `src/FbActivity.php` — keep `getSubject`/`getSubjectName` for BC but delegate.

**Verify:** Livewire tests: list shows title not class name; view page shows
`Podcast ↣ title`; deleted subject row renders title; exporter states; fallback.
Use `assertTableColumnFormattedStateSet` (Task 2 note).
