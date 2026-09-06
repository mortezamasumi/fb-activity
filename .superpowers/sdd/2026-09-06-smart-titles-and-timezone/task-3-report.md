# Task 3 Report — HasActivityTitle contract + subject/causer resolver

**Status:** complete. Commit: "feat: fault-tolerant subject/causer title resolver
with per-model overrides".

**Changes:**

- `src/Contracts/HasActivityTitle.php`: optional contract `activityTitle(Activity): ?string`.
- `src/Support/ActivitySubjectResolver.php`: full D1 pipeline — config map
  (attr/dot-path/Closure/invokable/null), contract, Filament record title (miss when
  it equals the model label), attribute cascade (getAttribute → accessors +
  HasTranslations), deleted-recovery from `properties.attributes`/`old`, memoized per
  `title|type|id|locale`. `relationFor()` guards nonexistent morph classes;
  `getExtraProperty` calls guarded (null `properties` would throw);
  `guard()` reports + swallows all Throwables. Also `modelLabel()` (config → Filament
  resource → basename) and `subjectUrl()` skeleton (used in Task 5).
- `src/FbActivity.php`: `resolveSubjectTitle` / `resolveCauserTitle` (both fall back
  to `Label #id`), `subjectModelLabel`, `resolveSubjectUrl` delegating to the resolver.
- `src/FbActivityServiceProvider.php`: `FbActivity` + resolver bound as **singletons**
  — required for memoization to work across calls (facade previously resolved fresh
  instances each time).
- Tests: `tests/Tests/FbActivityResolverTest.php` (16 tests) + fixtures
  `TranslatableThing` (HasTranslations + implements the contract, factory) and a
  `translatable_things` table in TestCase.

**Verify:** 42 tests / 110 assertions; phpstan clean (fixed 6: memo null-coalesce,
contract instanceof narrow, Htmlable handling on record titles, closure var capture,
iterable value PHPDoc); pint clean.

**Deviations/notes:**

- Test originally expected a config key of `Activity::class` to match an activity
  whose `subject_type` is `Podcast` — wrong on my side; the map is keyed by subject
  type only. Test updated to document that semantics explicitly.
- Filament step: `Filament::getPanels()` scanned across ALL registered panels
  (current panel first is not guaranteed; panel order is registration order). Fine
  for now; note if ambiguity matters later.
- `assertTableColumnFormattedStateSet` note from Task 2 applies to Task 4 tests.
