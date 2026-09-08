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

## Visibility is about listing, not access

`public` controls whether a case appears in the public list. It does not control who may read it:
`GemaraCaseController::show()` has no authorization, so any case is served in full to anyone who
requests its URL, including unauthenticated guests.

**This is deliberate.** Cases are meant to be shareable — a link to a case must work for whoever it
is sent to, without an account and without the author having to publish it to the world first.
"Not public" therefore means *unlisted*, not *unreadable*.

The consequence worth knowing: case ids are sequential, so unlisted cases can be found by walking
`/gemara_cases/1..N`. Unlisted is not the same as unguessable. If that ever matters, a random slug
on the URL would preserve shareable links while removing enumerability; it is not a concern today
because nothing in a case is sensitive.

```gherkin
Scenario: VIEW-09 - An unlisted case is still readable by anyone with its URL   (observed)
  Given a case marked not public
  When someone who does not own it requests its URL
  Then they receive the case in full
  And the case does not appear in the public list
```
