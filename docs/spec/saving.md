# Saving a case

Saving is gated three ways, in this order: you must be **logged in** to see the buttons at all, the
case must be **complete** for them to be enabled, and the server re-validates everything on arrival.
The client-side gate exists to explain *what* is missing, not to be trusted.

## The completeness gate

`caseComplete()` returns true only when `getValidationErrors()` is empty. Every one of the eight
conditions must be either answered or marked Not Relevant, and the masechet, daf, gemara text, din
type and act must all be set. While incomplete, the Save button is disabled and a small red **?**
appears beside it; clicking it opens a modal listing exactly what is missing, one line per problem
— e.g. *"You must fill in the Consequences or mark it N/R"*.

This is the only place the user is told why saving is unavailable, so the modal is load-bearing.

## Create, update, and clone

`submitCase()` decides the verb from `theCase.caseId`:

| State | Verb | URL |
|---|---|---|
| New case (`caseId` falsy) | POST | `/gemara_cases` |
| Editing (`caseId` set) | PUT | `/gemara_cases/{id}` |
| Editing with **Save as New** | POST | `/gemara_cases` (`caseId` zeroed first) |

**Save as New** only appears when editing an existing case. It clones by zeroing `caseId` so the
request becomes a create. `caseId` is restored afterwards in both the success and failure paths, so
a failed clone leaves the form still editing the original.

On success the browser navigates to `/gemara_cases/`. On a response carrying a `message` the raw
message is shown via `alert()` — that is the entire server-error surface.

## What the server receives

The browser posts camelCase with conditions nested:

```json
{ "masechet": "Berakhot", "daf": "7a", "gemaraText": "...", "dinType": "מותר",
  "act": "...", "public": false, "caseId": 0, "_token": "...",
  "inputConditions": { "who": { "value": "An adult", "notRelevant": false }, ... } }
```

`GemaraCaseRequest::prepareForValidation()` flattens that into columns: `gemaraText` → `gemara_text`,
`dinType` → `din_type`, and each condition into `{name}` plus `{name}_nr`. See
`input-conditions.md` for what happens to a Not Relevant value on the way through.

---

## Scenarios

```gherkin
Feature: Saving

  Scenario: SAVE-01 - A guest is told to log in instead of being offered save   (observed)
    Given I am not logged in
    And I have filled in a complete case
    Then no save button is shown
    And I see "Please login to be able to save."

  Scenario: SAVE-02 - An unauthenticated POST is rejected                       (code)
    When I POST a valid case without authenticating
    Then I receive 401

  Scenario: SAVE-03 - Save is disabled until the case is complete               (observed)
    Given I am logged in
    And I have entered masechet, daf, gemara text, din type and act
    But the eight conditions are all empty
    Then the save button is disabled
    And the red "?" badge is visible

  Scenario: SAVE-04 - The badge explains what is missing                        (observed)
    Given the case is incomplete
    When I click the red "?" badge
    Then a modal lists each missing item
    And it includes "You must fill in the Consequences or mark it N/R"

  Scenario: SAVE-05 - Completing every condition enables save                   (observed)
    Given the case is incomplete
    When I answer all eight conditions
    Then the save button is enabled

  Scenario: SAVE-06 - Saving redirects to the case list                         (observed)
    Given a complete case
    When I press save
    Then the case is stored
    And I am taken to "/gemara_cases/"

  Scenario: SAVE-07 - Every field round-trips into the database                 (code)
    When I save a complete case
    Then the stored row has the masechet, daf, gemara text, din type and act I entered
    And each of the eight conditions is stored with its value and its _nr flag

  Scenario: SAVE-08 - Editing an existing case updates it rather than duplicating (code)
    Given I own a saved case
    When I open it and save
    Then the request is a PUT to that case's URL
    And no second case is created

  Scenario: SAVE-09 - Save as New is offered only when editing                  (observed)
    Given I am creating a new case
    Then the "Save as New" button is not shown
    Given I am editing a saved case
    Then the "Save as New" button is shown

  Scenario: SAVE-10 - Save as New clones rather than updating                   (code)
    Given I am editing a saved case
    When I press "Save as New"
    Then a second case is created
    And the original is unchanged
```

```gherkin
Feature: Save validation

  Scenario: SAVE-11 - A missing required field is rejected                      (code)
    When I POST a case with no act
    Then I receive 422

  Scenario: SAVE-12 - An unknown masechet is rejected                           (code)
    When I POST a case whose masechet is not in the tractates table
    Then I receive 422
    # the 'exists:tractates,english_name' rule

  Scenario: SAVE-13 - An invalid din type is rejected                           (code)
    When I POST a case with a din type outside the six adjudications
    Then I receive 422

  Scenario: SAVE-14 - An unanswered, non-N/R condition is rejected              (code)
    When I POST a case where "who" is empty and not marked Not Relevant
    Then I receive 422

  Scenario: SAVE-15 - An N/R condition with no value is accepted                (code)
    When I POST a case where "who" is empty and marked Not Relevant
    Then the case is created
```

## Known defects

```gherkin
@defect
Scenario: SAVE-16 - A server error is reported usefully
  # The only error surface is alert(result.message). A 422 returns a Laravel
  # validation payload whose 'message' is a summary string, so the user gets a
  # browser alert with no indication of which field failed. The .catch() branch
  # reports nothing at all -- a network failure silently does nothing.
  Given saving fails validation on the server
  When the response comes back
  Then I am shown which fields were rejected

@defect
Scenario: SAVE-17 - Update targets the case named in the URL
  # GemaraCaseController::update() ignores its route-model-bound $gemaraCase and
  # re-finds the case by $request['caseId'], then returns the *bound* model as
  # JSON. authorize() happens to check caseId, so this is not currently
  # exploitable, but the write and the response refer to different rows.
  Given I PUT to /gemara_cases/{id}
  Then the case updated is the one identified by {id}
```
