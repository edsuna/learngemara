# Viewing a case

There is only one case template. `create`, `show` and `edit` all render
`gemara_case.create`; what differs is a single boolean, `allowUpdates`, computed in
`GemaraCaseController::showForm()`:

```php
'allowUpdates' => !$gemaraCase || ($gemaraCase->user_id === Auth::id())
```

So "read-only" is not a separate view — it is the same page with every control suppressed. That
keeps the diagram identical between the person who wrote it and everyone else, which matters
because the diagram is the artifact being shared.

## What read-only actually suppresses

With `allowUpdates` false: the masechet and daf selects, the gemara textarea, every condition
input, every Not Relevant checkbox, the din type select, and both save buttons. What remains is
the values as dashed-underlined text, the eight ovals, the act, the din box, and all eight arrows.
`submitCase()` also returns early on `!allowUpdates`, so the form cannot be submitted even if the
markup were manipulated.

A guest viewing a case sees the same read-only rendering plus the "Please login to be able to
save." prompt.

## The `?hideicons` display mode

`showForm()` threads `$request->query('hideicons')` into the template, which suppresses the PNG
icon in each condition oval while keeping the labels. It is the existing precedent for a compact
rendering of the diagram, and worth knowing about before inventing a new one.

## Arrows on a loaded case

Opening a saved case runs `initCaseData()` — the snake_case → camelCase hydration of all eight
conditions — and then draws the arrows on a 250 ms timer. This is the path with the least test
coverage historically and the one most likely to break if the case JSON shape changes: a renamed
field yields `undefined`, and `selectMasechet()` then throws on `masechet.numberOfDapim`.

---

## Scenarios

```gherkin
Feature: Viewing a case

  Scenario: VIEW-01 - Anyone can open a case and see its diagram              (observed)
    Given a saved case
    When a guest opens "/gemara_cases/{id}"
    Then the page renders without error
    And the eight condition ovals and the act oval are visible
    And all 8 arrows are drawn

  Scenario: VIEW-02 - A non-owner gets no editing controls                    (observed)
    Given a saved case I do not own
    When I open it
    Then the masechet select is not visible
    And the act input is not visible
    And no Not Relevant checkboxes are visible
    And no save button is visible

  Scenario: VIEW-03 - Stored values are rendered as text                      (observed)
    Given a saved case I do not own
    When I open it
    Then the act is shown as text
    And each answered condition shows its stored value

  Scenario: VIEW-04 - A guest viewing a case is invited to log in             (observed)
    Given a saved case
    When a guest opens it
    Then I see "Please login to be able to save."

  Scenario: VIEW-05 - The owner gets the editable form                        (code)
    Given a case I own
    When I open "/gemara_cases/{id}/edit"
    Then the save button is present

  Scenario: VIEW-06 - Editing requires authentication                         (observed)
    Given a saved case
    When a guest opens "/gemara_cases/{id}/edit"
    Then they are redirected to the login page

  Scenario: VIEW-07 - hideicons suppresses icons but keeps labels             (observed)
    When I open a case with "?hideicons=1"
    Then no condition oval shows its icon image
    And each condition oval still shows its label

  Scenario: VIEW-08 - A saved case draws its arrows after hydration           (observed)
    Given a saved case with an act and a din type
    When I open it
    Then all 8 arrows connect their condition oval to the act oval
```

## Known defects

```gherkin
@defect
Scenario: VIEW-09 - A private case is not readable by strangers
  # The `public` flag controls *listing*, not *access*. GemaraCaseController::show()
  # has no authorization: a case marked private is excluded from the public list
  # but is served in full to anyone who requests its URL, including
  # unauthenticated guests. Verified: a guest fetching a private case received
  # 200 and the case's title in the response body.
  #
  # Ids are sequential, so every private case in the system can be enumerated by
  # walking /gemara_cases/1..N.
  #
  # NOTE: fixing this is a product decision, not just a code change -- if
  # unlisted-but-shareable links are relied on, enforcing ownership would break
  # them. Needs a decision before it is changed.
  Given a case marked not public
  When someone who does not own it requests its URL
  Then they do not receive its contents
```
