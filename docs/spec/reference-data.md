# Reference data — tractates and dapim

The masechet and daf pickers are driven by the `tractates` table, served as raw model JSON from
`GET /api/tractates` (unauthenticated). The daf list is **not** stored; it is generated in the
browser from the tractate's page count.

## Tractate shape

| Column | Meaning |
|---|---|
| `name` | Hebrew name, e.g. `ברכות` |
| `english_name` | Unique; underscores for spaces, e.g. `Bava_Kamma`. The de-facto foreign key — `gemara_cases.masechet` references it. |
| `pages` | Number of dapim |
| `has_last_amud` | Whether the final daf has an amud bet |

The endpoint returns snake_case columns, which `getMasechtot()` remaps to camelCase
(`english_name` → `englishName`, `name` → `text`, `pages` → `numberOfDapim`,
`has_last_amud` → `hasLastAmud`).

## How the daf list is generated

A tractate begins at daf 2 — there is no daf 1 — and each daf has two amudim, `a` and `b`. The
final daf usually has no amud bet, so it is omitted unless `has_last_amud` is set.

For Berakhot (`pages` = 64, `has_last_amud` = 0) this yields 125 entries, `2a` through `64a`.

Option **values** are always English (`7a`, `7b`) regardless of display language, because they are
what gets persisted. Option **labels** follow the display language: English shows `7a`, Hebrew
shows gematriya with a period for amud alef and a colon for amud bet — `ז׳.` and `ז׳:`.

---

## Scenarios

```gherkin
Feature: Tractate reference data

  Scenario: REF-01 - The tractate list is publicly readable               (code)
    When I request "/api/tractates" without authenticating
    Then I receive 200
    And the response is a JSON array of tractates

  Scenario: REF-02 - A single tractate can be fetched by english_name     (code)
    When I request "/api/tractates/Eiruvin"
    Then I receive 200
    And the response has english_name "Eiruvin"

  Scenario: REF-03 - An unknown tractate is not found                     (code)
    When I request "/api/tractates/Nonexistent"
    Then I receive 404

  Scenario: REF-04 - The masechet picker is populated from the API        (observed)
    Given I open the case create form
    Then the masechet select offers one option per seeded tractate, plus a placeholder
```

```gherkin
Feature: Daf generation

  Scenario: REF-05 - Dapim start at 2a                                    (observed)
    When I select the masechet "Berakhot"
    Then the first daf option has value "2a"

  Scenario: REF-06 - The last amud bet is omitted unless the tractate has one  (observed)
    When I select the masechet "Berakhot"
    # pages = 64, has_last_amud = 0
    Then the last daf option has value "64a"
    And there are 125 daf options

  Scenario: REF-07 - Daf values are English regardless of language        (observed)
    Given the display language is Hebrew
    When I select a masechet
    Then the daf option values are still of the form "7a" / "7b"

  Scenario: REF-08 - Hebrew labels dapim in gematriya                     (observed)
    Given the display language is Hebrew
    When I select the masechet "Shabbat"
    Then the first three daf labels are "ב׳.", "ב׳:", "ג׳."
    # period marks amud alef, colon marks amud bet

  Scenario: REF-09 - English labels dapim in digits                       (observed)
    Given the display language is English
    When I select the masechet "Shabbat"
    Then the first three daf labels are "2a", "2b", "3a"
```

## Known defects

```gherkin
Scenario: REF-10 - Switching language relabels the daf list              (observed)
  # fillInDapim() bakes the active language into the labels, so the list has
  # to be rebuilt when the language changes or this one control keeps the
  # language it was created in while everything around it switches. Option
  # values are language-independent, so the current selection survives.
  Given I have selected the masechet "Shabbat" in English
  And the daf labels read "2a", "2b", "3a"
  When I switch the display language to Hebrew
  Then the daf labels read "ב׳.", "ב׳:", "ג׳."
```

## Note

`initDapim()` is a fallback that assumes 176 dapim with a final amud bet, used before a tractate
is known. It is not reachable through the normal flow, which always selects a masechet first.
