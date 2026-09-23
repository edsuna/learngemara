# Case list

Two lists share one Livewire component, distinguished by a mount parameter:

- `/gemara_cases` — **my cases**, scoped to `user_id = Auth::id()`
- `/gemara_cases?public=1` — **public cases**, scoped to `public = 1`

The component is `GemaraCases`, and the two scopes are mutually exclusive branches of one query —
the "my cases" list is not filtered by `public`, so a user sees their own private cases there.

## Ordering is by where the case sits in the Talmud

```php
->orderBy('tractates.id')     // canonical tractate order, not alphabetical
->orderByRaw('daf * 1')       // numeric: 7a before 10a
->orderBy('daf')              // then amud: 7a before 7b
```

`daf` is a string (`"7a"`), so `daf * 1` coerces its numeric prefix to sort 7 before 10 — a plain
string sort would put `"10a"` before `"7a"`. Both clauses are needed: the first orders by daf
number, the second by amud within a daf. This ordering is why the list joins `tractates` at all.

## The join and its column collision

```php
GemaraCase::join('tractates', 'gemara_cases.masechet', '=', 'tractates.english_name')
    ->select('gemara_cases.id as case_id', 'gemara_cases.*', 'tractates.*')
```

`tractates.*` is what supplies `$case->name`, the Hebrew tractate name shown in the Hebrew view.
Because both tables have `id`, `gemara_cases.id` is aliased to `case_id` — every row action uses
`$case->case_id`, not `$case->id`. Any additional join must preserve that aliasing or the delete
and edit actions will silently target the wrong ids.

## Filtering and search

Masechet and daf are `#[Url]` properties, so the current filter is reflected in the address bar and
a filtered list can be linked. The filter controls themselves are Alpine, not Livewire — they sit
inside `wire:ignore` and push values across with `$wire.set(...)`, debounced 300 ms for the search
box. The rows are server-rendered.

Search matches `gemara_text`, `title`, `act`, and all eight condition columns, all with `LIKE %…%`,
grouped so the scope and filters still apply.

Pagination is 25 per page.

## Deleting is two-step

The trash icon calls `confirmRemove(id)`, which sets `selectedId` and swaps the row's controls for
a confirm/cancel pair. Only the second click calls `removeGemaraCase(id)`. The delete controls are
rendered only for the owner — and the action itself enforces ownership
server-side (see `authorization.md`).

---

## Scenarios

```gherkin
Feature: Case list

  Scenario: LIST-01 - The public list shows public cases to anyone            (observed)
    Given there are public cases
    When a guest opens "/gemara_cases?public=1"
    Then the list renders
    And it shows up to 25 rows

  Scenario: LIST-02 - The public list excludes private cases                  (observed)
    Given a case marked not public
    When anyone opens the public list
    Then that case is not listed

  Scenario: LIST-03 - My list shows only my own cases                         (code)
    Given I own one case and another user owns another
    When I open "/gemara_cases"
    Then only my case is listed

  Scenario: LIST-04 - My list includes my private cases                       (code)
    Given I own a case marked not public
    When I open "/gemara_cases"
    Then that case is listed

  Scenario: LIST-05 - Cases are ordered by tractate, then daf, then amud      (observed)
    Given cases on Berakhot 2a, Berakhot 10a, Berakhot 7b and Shabbat 2a
    Then they are listed in the order: Berakhot 2a, Berakhot 7b, Berakhot 10a, Shabbat 2a
    # "daf * 1" is what puts 7b before 10a; a string sort would not

  Scenario: LIST-06 - The list paginates at 25                                (observed)
    Given there are more than 25 visible cases
    Then the first page shows 25 rows
    And pagination controls are shown

  Scenario: LIST-07 - Filtering by masechet narrows the list                  (observed)
    When I filter by a masechet
    Then only cases from that masechet are listed

  Scenario: LIST-08 - Filtering by daf narrows the list                       (code)
    When I filter by masechet and daf
    Then only cases on that daf are listed

  Scenario: LIST-09 - The active filter is in the URL                         (code)
    When I filter by masechet
    Then the masechet appears as a query parameter
    And opening that URL directly reproduces the filtered list

  Scenario: LIST-10 - Changing masechet returns to page one                   (code)
    Given I am on page 2
    When I change the masechet filter
    Then I am on page 1

  Scenario: LIST-11 - Search matches the gemara text                          (code)
    Given a case whose gemara text contains "unique phrase"
    When I search for "unique phrase"
    Then that case is listed

  Scenario Outline: LIST-12 - Search also matches title, act and conditions   (code)
    Given a case whose <field> contains "unique phrase"
    When I search for "unique phrase"
    Then that case is listed

    Examples:
      | field        |
      | title        |
      | act          |
      | who          |
      | consequences |

  Scenario: LIST-13 - A search matching nothing empties the list              (observed)
    When I search for a phrase no case contains
    Then no rows are listed

  Scenario: LIST-14 - The Hebrew view shows the Hebrew tractate name          (code)
    Given the display language is Hebrew
    Then each row shows the tractate's Hebrew name
    # $case->name, supplied by the tractates join
```

```gherkin
Feature: Deleting from the list

  Scenario: LIST-15 - Deleting takes two clicks                               (observed)
    Given I own a listed case
    When I click its delete control
    Then a confirm control appears
    And the case is still listed
    When I click the confirm control
    Then the case is no longer listed

  Scenario: LIST-16 - Cancelling a delete leaves the case alone               (code)
    Given I have clicked delete on a case
    When I cancel
    Then the case is still listed

  Scenario: LIST-17 - Delete controls are not shown to non-owners             (observed)
    Given a case I do not own
    When I view it in the public list
    Then no delete control is shown for it
```
