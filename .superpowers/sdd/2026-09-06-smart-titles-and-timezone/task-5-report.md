# Task 5 Report — Subject links

**Status:** complete. Commits: "feat: project-adaptable subject links via config map
or Filament view page" + "fix: align fallback label expectations with Filament
resource kebab labels".

**Changes:**

- `ActivitySubjectResolver::subjectUrl()` (built in Task 3, now finalized): config
  map (`{id}` pattern | route name | Closure | invokable) → Filament resource view
  page (`hasPage('view')` checked before `getUrl` to avoid noisy exceptions) → null.
  Memoized, guarded, gated by `subject.link.enabled`.
- `FbActivitiesTable`: subject column `->url(...)` + `->color('primary')` only when
  a URL resolves; plain text otherwise.
- Tests: 7 new URL tests (pattern, route name, closure, Filament view page, disabled,
  broken route, no-source). Test fixtures: `PodcastResource` (+ `ManagePodcasts`,
  `ViewPodcast` pages) registered in the TestCase panel — this also made the Filament
  step live for ALL resolver tests, exposing that Filament's default
  `getModelLabel()` is kebab-case lowercase ("podcast"); the 5 fallback expectations
  were aligned accordingly (spec label order confirmed: config → Filament label →
  basename headline).

**Verify:** 57 tests / 137 assertions; phpstan clean; pint clean.

**Note:** apps that prefer "Podcast" casing in fallbacks should set
`fb-activity.subject.labels[Model::class]` — documented in Task 6's README step.
