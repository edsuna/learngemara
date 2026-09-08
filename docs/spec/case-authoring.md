# Case authoring

Creating a case is a **progressive reveal**: nothing is shown until the step before it is answered.
This is deliberate teaching design — the order in which the form opens up is the order in which the
analytic skillset is meant to be applied. You locate the text, decide the adjudication, name the
act, and only then decompose the conditions that produce it.

The reveal order is: masechet → daf → gemara text → din type → act → the eight condition ovals.

## Answered fields collapse into text

Each of masechet, daf, din type and act is a control **only until it is answered**. Once answered,
the control is hidden and the value is shown as text with a dashed underline. Changing an answer
means double-clicking that text to reset it, which brings the control back.

This is not obvious from the UI and is easy to break: the select carries
`x-show="allowUpdates && !theCase.masechet"`, so it disappears the moment a value exists. A test
that expects to change masechet by using the dropdown a second time will find it invisible.

Resetting cascades. Resetting the masechet clears the daf, because the daf list belongs to the
chosen tractate.

## The gemara text is entered by the user

The textarea is the user's own transcription of the statement being analyzed. It is not populated
from Sefaria automatically. A separate **Show Amud Text** button fetches the full amud from
`https://www.sefaria.org/api/texts/{Masechet}.{daf}` and displays it in a modal for reference, so
the user can read the daf while deciding what to enter.

The textarea commits on blur: `theCase.gemaraText = tmpSelectedText`. Until it blurs, the diagram
does not appear.

---

## Scenarios

```gherkin
Feature: Case authoring - progressive reveal

  Scenario: CASE-01 - Only the masechet picker is offered initially       (observed)
    Given I open the case create form
    Then the masechet select is visible
    And the daf select is not visible

  Scenario: CASE-02 - Choosing a masechet reveals the daf picker          (observed)
    When I select the masechet "Berakhot"
    Then the daf select is visible
    And the masechet select is no longer visible
    And "Berakhot" is shown as text

  Scenario: CASE-03 - Choosing a daf hides the daf picker                 (observed)
    Given I have selected the masechet "Berakhot"
    When I select the daf "7a"
    Then the daf select is no longer visible
    And "7a" is shown as text

  Scenario: CASE-04 - Double-clicking the masechet resets it              (observed)
    Given I have selected the masechet "Berakhot" and the daf "7a"
    When I double-click the masechet text
    Then the masechet select is visible again
    And the daf is cleared
    And the daf select is not visible
    And the gemara text is cleared
    # resetCase() -> resetDaf() -> resetDafText(); the whole downstream
    # answer chain is discarded, not just the daf

  Scenario: CASE-05 - The diagram stays hidden until gemara text is committed  (observed)
    Given I have selected a masechet and a daf
    Then the diagram container is not visible
    When I type gemara text and blur the textarea
    Then the diagram container is visible

  Scenario: CASE-06 - The din type offers exactly the six adjudications   (observed)
    Given I have entered gemara text
    Then the din type select offers 7 options
    # a placeholder plus מותר, אסור, חייב, פטור, כשר, פסול

  Scenario: CASE-07 - The act input appears once a din type is chosen     (observed)
    Given I have entered gemara text
    When I select the din type "מותר"
    Then the act input is visible

  Scenario: CASE-08 - Entering the act reveals the condition ovals        (observed)
    Given I have selected a din type
    When I enter an act and blur
    Then all eight condition ovals are visible
```

```gherkin
Feature: Amud text lookup

  Scenario: CASE-09 - Show Amud Text fetches the daf from Sefaria         (observed)
    Given I have selected the masechet "Berakhot" and the daf "2a"
    When I press Show Amud Text
    Then a request is made to sefaria.org for "Berakhot.2a"
    And a modal opens containing the Hebrew text of that amud

  Scenario: CASE-10 - The amud text does not populate the gemara text     (observed)
    Given the amud text modal is open
    Then the gemara text field is still empty
    # the user transcribes the statement themselves; the modal is reference only

  Scenario: CASE-11 - Changing the daf discards a loaded amud text        (code)
    Given I have loaded the amud text
    When I select a different daf
    Then the loaded amud text is cleared
```

## Known defects

```gherkin
Scenario: CASE-12 - A failed Sefaria lookup tells the user               (observed)
  # The modal is gated on amudText being truthy, so a failure has to put a
  # message there or the button appears to do nothing at all. Sefaria is a
  # third party on the open internet; this will happen.
  Given Sefaria is unreachable
  When I press Show Amud Text
  Then I am told the amud text could not be loaded
```
