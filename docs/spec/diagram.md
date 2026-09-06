# Diagram

The diagram is the analytic skillset tool: it renders one halacha as eight **input condition**
ovals feeding a central **act** oval, which in turn feeds a **דין** (adjudication) box below.
Reading it, a learner should see at a glance which conditions the ruling depends on.

The arrows are not decoration. They are the claim the diagram makes — *these conditions produce
this adjudication for this act* — so an arrow pointing at nothing is a broken statement, not a
cosmetic glitch.

## Structure

Eight input conditions, in the order declared by `GemaraCase::inputConditions`:

`consequences`, `when`, `where`, `toWhat`, `withWhat`, `how`, `other`, `who`

They are laid out in a 6-column grid: the first six on row 1, `other` and `who` on row 2 (`who`
pinned to column 6), and the act oval spanning row 3.

### Arrow anchor map

Each arrow leaves its condition oval on a named side and arrives at a named side of one of three
anchors on the act oval — `gc-act` itself, or the invisible spacer divs `gc-act-top` and
`gc-act-inner`. This map is `resources/js/arrows.js` and is reproduced here because a replacement
implementation must preserve it exactly:

| Condition | Leaves | Arrives | Anchor |
|---|---|---|---|
| `consequences` | RIGHT | TOP_LEFT | `gc-act-inner` |
| `when` | BOTTOM | TOP_LEFT | `gc-act-top` |
| `where` | BOTTOM | TOP | `gc-act` |
| `toWhat` | BOTTOM | TOP | `gc-act` |
| `withWhat` | BOTTOM | TOP_RIGHT | `gc-act-top` |
| `how` | LEFT | TOP_RIGHT | `gc-act-inner` |
| `other` | BOTTOM | LEFT | `gc-act` |
| `who` | BOTTOM | RIGHT | `gc-act` |

Arrowheads are `HEAD.NORMAL`, size 10, drawn at 0.99 along the path.

## Arrows track their anchors continuously

This is the behavior most at risk from reimplementation, and the least visible in the source.

Arrows are `<svg class="arrow">` elements appended to `document.body`, positioned
`position: fixed` in **viewport** coordinates — not placed inside the diagram container. They stay
attached to their ovals because `arrows-svg` runs a permanent `requestAnimationFrame` loop per
arrow, re-reading `getBoundingClientRect()` of both endpoints every frame and repositioning.

That loop *is* the resize handling, the RTL handling, and the scroll handling. There is no resize
listener anywhere in the codebase, because none is needed. Any replacement that draws arrows once
will satisfy DIAG-10 and fail DIAG-11 through DIAG-15.

The layout moves more often than it first appears:

- **Window resize** reflows the 6-column grid and changes oval widths.
- **Language toggle** flips the page to RTL *and* substitutes Hebrew labels of different length,
  changing oval heights. (The diagram itself is forced `.ltr` so column order stays fixed, but it
  still reflows.)
- **Entering text** into a condition replaces an `<input>` with a text span of different height.
- **Marking Not Relevant** restyles the oval.
- **Scroll** changes viewport coordinates for every `position: fixed` element.

---

## Scenarios

```gherkin
Feature: Diagram rendering

  Background:
    Given I am on the case create form
    And I have selected a masechet and daf
    And I have entered gemara text

  Scenario: DIAG-01 - The diagram is hidden until there is gemara text     (observed)
    Given I have not entered gemara text
    Then the diagram container is not visible

  Scenario: DIAG-02 - The act oval appears once a din type is chosen       (observed)
    When I select a din type
    Then the act oval is visible
    And the eight condition ovals are not visible

  Scenario: DIAG-03 - Condition ovals appear once the act is entered       (observed)
    Given I have selected a din type
    When I enter an act
    Then all eight condition ovals are visible

  Scenario: DIAG-04 - Every input condition has an oval                    (observed)
    Then there is one oval for each of consequences, when, where, toWhat,
      withWhat, how, other, who

  Scenario: DIAG-05 - A Not Relevant condition is dimmed but still shown   (observed)
    When I mark "who" as Not Relevant
    Then the "who" oval is still visible
    And the "who" oval is rendered at reduced opacity

  Scenario: DIAG-06 - Condition icons render by default                   (observed)
    Then each condition oval shows its icon image

  Scenario: DIAG-07 - hideicons suppresses the icons                      (observed)
    Given I open the create form with "?hideicons=1"
    Then no condition oval shows an icon image
    And the oval labels are still shown
```

```gherkin
Feature: Diagram arrows

  Background:
    Given I am on the case create form
    And I have selected a masechet and daf
    And I have entered gemara text
    And I have selected a din type

  Scenario: DIAG-08 - No arrows before the act is entered                 (observed)
    Then there are 0 arrows on the page

  Scenario: DIAG-09 - Entering the act draws all eight arrows             (observed)
    When I enter an act
    Then there are 8 arrows on the page

  Scenario: DIAG-10 - Each arrow connects its condition to the act        (observed)
    Given I have entered an act
    Then for each of the eight conditions there is an arrow whose extent
      reaches both that condition's oval and the act oval

  Scenario Outline: DIAG-11 - Arrows stay connected across viewport widths (observed)
    Given I have entered an act
    When the viewport width becomes <width>
    Then all 8 arrows still connect their condition oval to the act oval

    Examples:
      | width |
      | 900   |
      | 1400  |
      | 1600  |

  Scenario: DIAG-12 - Arrows stay connected after switching to Hebrew     (observed)
    Given I have entered an act
    When I toggle the language to Hebrew
    Then all 8 arrows still connect their condition oval to the act oval

  Scenario: DIAG-13 - Arrows stay connected after switching back          (observed)
    Given I have entered an act
    And I have toggled the language to Hebrew
    When I toggle the language back to English
    Then all 8 arrows still connect their condition oval to the act oval

  Scenario: DIAG-14 - Arrows stay connected after scrolling               (observed)
    Given I have entered an act
    When I scroll the page down 300 pixels
    Then all 8 arrows still connect their condition oval to the act oval

  Scenario: DIAG-15 - Arrows follow an oval that grows                    (observed)
    Given I have entered an act
    When I enter a long value into the "where" condition
    Then there are still 8 arrows
    And all 8 arrows still connect their condition oval to the act oval

  Scenario: DIAG-16 - Marking a condition Not Relevant keeps its arrow    (observed)
    Given I have entered an act
    When I mark "who" as Not Relevant
    Then there are still 8 arrows

  Scenario: DIAG-17 - Clearing the act removes every arrow                (observed)
    Given I have entered an act
    When I double-click the act text to reset it
    Then there are 0 arrows on the page

  Scenario: DIAG-18 - Re-entering the act redraws the arrows              (observed)
    Given I have entered an act and then cleared it
    When I enter an act again
    Then there are 8 arrows on the page

  Scenario: DIAG-19 - Opening a saved case draws its arrows               (observed)
    Given a saved case with an act and a din type
    When I open that case
    Then there are 8 arrows on the page
    And all 8 arrows connect their condition oval to the act oval
```

## Known defects

```gherkin
@defect
Scenario: DIAG-20 - Every condition oval is vertically aligned by its rule
  # app.css styles ".gc-with-what", but the blade emits class "gc-withWhat"
  # (input conditions are camelCase), so the rule matches nothing. There is
  # no ".gc-toWhat" rule at all. Both ovals silently lose their alignment.
  Given the diagram is fully rendered
  Then the "withWhat" oval is vertically aligned like the "when" oval
  And the "toWhat" oval is vertically aligned like the "where" oval

@defect
Scenario: DIAG-21 - Arrow observers are released when arrows are removed
  # arrowCreate() returns a { node, clear } pair; initArrows() keeps only
  # node and discards clear, so every arrow leaks its requestAnimationFrame
  # observer. resetArrows() removes the SVG but the loop keeps running.
  # Eight per draw; a comparison view showing several diagrams multiplies it.
  Given I have entered an act and then cleared it
  Then no requestAnimationFrame observer for the removed arrows is still running
```

## Notes for reimplementation

`resetArrows()` is `document.querySelectorAll('.arrow').forEach(e => e.remove())` — a global wipe
with no notion of which diagram it belongs to. Combined with `document.body` mounting and literal
element ids (`gc-act`, `gc-when`, …), only one diagram can exist per page today. Scenarios
DIAG-11 to DIAG-15 are the ones that constrain how a scalable replacement may be built: it must
reposition on layout change, by any mechanism.
