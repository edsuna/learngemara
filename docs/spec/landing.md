# Landing page

`/` renders `welcome.blade.php` through the invokable `Home` controller. It is a signpost, not an
application surface: a title, a logo, the language toggle, auth links, and two entry points into
the app.

The two entry points correspond to the two things a visitor might want — to *use* the analytic
tool, or to *read* what other people have analyzed.

---

## Scenarios

```gherkin
Feature: Landing page

  Scenario: HOME-01 - The page identifies itself                              (observed)
    When I open "/"
    Then the heading reads "How to Learn Gemara"
    And the logo image is shown

  Scenario: HOME-02 - Both entry points are offered                           (observed)
    When I open "/"
    Then there is a link to the analytic skillset tool
    And there is a link to the public Gemara cases

  Scenario: HOME-03 - A guest is offered login and registration               (observed)
    Given I am not logged in
    When I open "/"
    Then there are "Log in" and "Register" links

  Scenario: HOME-04 - A logged-in user is offered their own cases             (code)
    Given I am logged in
    When I open "/"
    Then there is a "My Gemara Cases" link
    And there is a logout control

  Scenario: HOME-05 - The heading is translated                               (observed)
    Given the display language is English
    Then the heading reads "How to Learn Gemara"
    When I switch the display language to Hebrew
    Then the heading reads "איך לומדים הלמדנים"

  Scenario: HOME-06 - The language toggle names the other language            (observed)
    Given the display language is English
    Then the toggle reads "To Hebrew"
    When I switch the display language to Hebrew
    Then the toggle reads "לאנגלית"
```
