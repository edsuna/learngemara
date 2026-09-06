# Input conditions

Each of the eight conditions holds one of three states: **unanswered**, **answered with a value**,
or **marked Not Relevant**. The distinction between the last two matters analytically — "this
ruling does not depend on when it happened" is a finding, not an omission — which is why N/R is an
explicit answer rather than simply leaving the field blank.

## The three-value state

Each condition is stored as `{ value, notRelevant }`, alongside a separate **draft buffer**
(`tmpCase`) holding what the user is currently typing.

| State | `value` in the browser | `value` in the database | `notRelevant` | Shown |
|---|---|---|---|---|
| Unanswered | `""` | `NULL` | `false` | text input + N/R checkbox |
| Answered | the text | the text | `false` | the value, dashed-underlined |
| Not Relevant | `" "` (a single space) | `NULL` | `true` | "Not Relevant", dashed-underlined |

**The browser and the database disagree about Not Relevant, and that gap has bitten before.**
Ticking the box sets `value` to a single space — enough to satisfy `required` on the way in. But
Laravel's default `TrimStrings` middleware reduces that space to `""`, and
`ConvertEmptyStringsToNull` then stores `NULL`. So a Not Relevant condition round-trips as
`value = NULL, notRelevant = true`, and the space never reaches the database.

That makes `notRelevant` the *only* record that the condition was deliberately dismissed rather
than left blank. Everything downstream depends on that flag surviving intact — see COND-10.

## Typing does not commit

The input binds to the draft buffer and only commits on the `change` event
(`updateCase()` copies `tmpCase.X` into `inputConditions.X.value`). Until then the value is still
empty and the diagram still shows the input. A test that fills a field with Playwright's `fill()`
and asserts the value landed will fail unless it dispatches `change` — `fill()` alone does not.

## Reset is the universal edit gesture

There is no "edit" affordance. Once a condition is answered, its input is replaced by text, and
the way back is to **double-click the text**, which calls `resetCasePiece()` and clears both
`value` and `notRelevant`.

This matters most for Not Relevant. Checking the N/R box sets `value` to `" "`, and the checkbox
lives inside a block shown only when `value` is empty — so **checking the box hides the box**. The
only way to un-mark a condition is to double-click the "Not Relevant" text. That is consistent
with how every other answered field in this form behaves, but it is not discoverable, and anyone
reimplementing the control is likely to assume the checkbox toggles.

---

## Scenarios

```gherkin
Feature: Input condition states

  Background:
    Given I am on the case create form with a fully revealed diagram

  Scenario: COND-01 - A condition starts unanswered                      (observed)
    Then the "who" condition has an empty value
    And the "who" condition is not marked Not Relevant
    And the "who" text input is visible

  Scenario: COND-02 - Typing alone does not commit the value             (observed)
    When I type "An adult" into the "who" input
    Then the "who" value is still empty
    And the "who" text input is still visible

  Scenario: COND-03 - Committing replaces the input with the value       (observed)
    When I type "An adult" into the "who" input
    And the input fires a change event
    Then the "who" value is "An adult"
    And the "who" text input is no longer visible
    And "An adult" is shown in the oval

  Scenario: COND-04 - Marking Not Relevant stores a single space         (observed)
    When I check Not Relevant for "where"
    Then the "where" notRelevant flag is true
    And the "where" value is exactly " "
    And "Not Relevant" is shown in the oval

  Scenario: COND-05 - Marking Not Relevant hides its own checkbox        (observed)
    When I check Not Relevant for "where"
    Then the "where" Not Relevant checkbox is no longer visible
    # value is now non-empty, and the checkbox is inside the
    # x-show="!value" block. Recovery is COND-06.

  Scenario: COND-06 - Double-clicking Not Relevant clears it             (observed)
    Given "where" is marked Not Relevant
    When I double-click the "Not Relevant" text
    Then the "where" value is empty
    And the "where" notRelevant flag is false
    And the "where" text input is visible again
    And the "where" Not Relevant checkbox is visible again

  Scenario: COND-07 - Double-clicking a value clears it                  (observed)
    Given the "who" condition has the value "An adult"
    When I double-click the "An adult" text
    Then the "who" value is empty
    And the "who" text input is visible again

  Scenario: COND-08 - A Not Relevant oval is dimmed                      (observed)
    When I check Not Relevant for "who"
    Then the "who" oval is rendered at reduced opacity
    And the "who" oval is still visible
    And its arrow to the act oval is still drawn
```

## Behavior worth knowing

```gherkin
Scenario: COND-09 - Resetting a condition keeps the typed draft          (observed)
  # resetCasePiece() clears value and notRelevant but not the tmpCase draft,
  # so the reappearing input is pre-filled with the text that was just
  # cleared. The stored value is empty while the visible input is not; the
  # user must fire change again to re-commit it. Convenient for editing,
  # confusing if you meant to clear.
  Given the "who" condition has the value "An adult"
  When I double-click the "An adult" text
  Then the "who" value is empty
  And the "who" text input is pre-filled with "An adult"
```

```gherkin
Scenario: COND-10 - A Not Relevant condition survives a save/reopen/save cycle  (observed)
  # Regression. Not Relevant persists as value NULL, and the _nr column came
  # back from MySQL as integer 1. `required_unless:{c}_nr,true` exempts only a
  # real boolean, so the reloaded 1 did not exempt the condition, its NULL
  # value then failed `required`, and re-saving an untouched case returned 422.
  # Re-ticking the checkbox sent a genuine boolean and appeared to fix it,
  # which made the bug look like a UI glitch rather than a type mismatch.
  # Fixed by casting the _nr columns to boolean on the model and adding a
  # `boolean` rule, which makes Laravel coerce the comparison.
  Given a saved case with "other" marked Not Relevant
  When I open that case for editing
  And I save it without changing anything
  Then the save succeeds
  And "other" is still marked Not Relevant

Scenario: COND-11 - Not Relevant flags reach the form as booleans           (observed)
  Given a saved case
  When the edit form hydrates from the case JSON
  Then every _nr flag is a boolean, not 1 or 0
```

## Localization note

The oval labels come from `localizedTexts.case{Condition}` — e.g. `caseWithWhat`, which contains
literal HTML (`'With Whom /<p></p>With What'`) and is therefore rendered with `x-html`, not
`x-text`. Any relabelling must keep that, or the markup will be shown as text.
