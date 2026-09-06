# Task 5 Brief — Subject links (project-adaptable)

**Files:**

- `src/Resources/Table/FbActivitiesTable.php` — subject column `->url()` via
  `FbActivity::resolveSubjectUrl()` + primary color when URL resolves.
- `tests/Tests/FbActivityResolverTest.php` — extend with URL tests: `{id}` pattern,
  route name, Closure, Filament view page, disabled flag → null, broken route → null.

**Filament view-page test prep:** the testbench panel has no Podcast resource with a
view page — register a minimal test resource in TestCase (or assert null for now and
cover Filament-url via a dedicated resource). Plan: create
`tests/Services/PodcastResource.php` managing Podcast with a `view` page registered
in a test panel; simplest: assert the Filament branch null when no resource exists,
and cover the Filament branch with a real minimal resource.
