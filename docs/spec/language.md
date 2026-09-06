# Language and direction

The whole UI switches between English and Hebrew. This is not a convenience feature — the subject
matter is Hebrew and Aramaic, and a learner working in Hebrew should not be reading English
scaffolding around it.

There is **no Laravel localization**. No `lang/` directory, no `__()` calls. Every string lives in
JavaScript object literals (`englishTexts` / `hebrewTexts`) inside the Alpine component that needs
it, selected by a `localizedTexts` getter. Two components carry their own copies:
`resources/js/language.js` (top bar) and `resources/js/gemara_case.js` (everything case-related).

## How the toggle propagates

The top bar's button calls `window.changeLanguage()`, which dispatches a `togglelanguage`
**CustomEvent on `window`**. Every Alpine root listens independently:

```blade
@togglelanguage.window="toggleLanguage()"
```

That event bus is the app's only cross-component communication mechanism. A new component that
displays text must listen for it or it will silently stay in the previous language.

Only `language.js`'s `toggleLanguage()` writes the `selectedLanguage` cookie. `gemara_case.js`'s
version flips its own state without persisting, relying on the top bar being present on the page.
A page that renders a case component without the top bar would toggle but not remember.

## Direction

Hebrew applies `.rtl` (`direction: rtl`) to the Alpine root. The diagram is the exception: it
carries a hard `.ltr` so its six-column grid keeps a fixed visual order in both languages —
otherwise the condition ovals would mirror and the arrow anchor map would no longer describe what
is on screen.

---

## Scenarios

```gherkin
Feature: Language toggle

  Scenario: LANG-01 - The interface starts in the cookie's language       (observed)
    Given the selectedLanguage cookie is "Hebrew"
    When I open the case create form
    Then the page is rendered right-to-left

  Scenario: LANG-02 - Toggling switches the interface                     (observed)
    Given the display language is English
    When I toggle the language
    Then the page is rendered right-to-left
    And the top bar button reads "לאנגלית"

  Scenario: LANG-03 - The choice is persisted                             (observed)
    Given the display language is English
    When I toggle the language
    Then the selectedLanguage cookie is "Hebrew"

  Scenario: LANG-04 - The choice survives a reload                        (observed)
    Given I have toggled the language to Hebrew
    When I reload the page
    Then the page is still rendered right-to-left
    And the selectedLanguage cookie is still "Hebrew"

  Scenario: LANG-05 - An unset cookie defaults to English                 (code)
    Given the selectedLanguage cookie is absent or the string "undefined"
    When I open any page
    Then the interface is in English

  Scenario: LANG-06 - The diagram stays left-to-right in Hebrew           (observed)
    Given a fully revealed diagram
    When I switch the display language to Hebrew
    Then the page is right-to-left
    And the diagram container is still left-to-right
    And the condition ovals appear in the same column order as in English

  Scenario: LANG-07 - Toggling does not lose entered data                 (observed)
    Given I have entered a masechet, daf, gemara text, din type and act
    When I toggle the language
    Then all of those values are still set
    And the diagram is still fully revealed
```

## Known defects

See `reference-data.md` REF-10: the daf dropdown labels are built once at masechet selection and
are not rebuilt on `togglelanguage`, so that one control keeps the language it was created in
while everything around it switches.

## Note for the sugya tool

Both `languageToggle()` and `gemaraCase()` carry an identical copy of the
`selectedLanguage` / `localizedTexts` getter pair. A third component would be a third copy. If the
sugya tool adds components, that getter is worth extracting once rather than duplicating again —
and whatever extracts it must keep the `togglelanguage` window event as the trigger, since that is
what every existing component is bound to.
