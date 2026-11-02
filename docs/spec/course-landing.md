# Course landing page

The course has its own address, `course.howtolearngemara.org`, separate from the tool at
`app.howtolearngemara.org`. Its home is the page a program head forwards to a prospective student,
so it must say what the course does for that person, show them a real analyzed case without asking
for an account, and offer one clear way to enroll.

The page does not pass through the tool's home page, and the tool's home page does not change:
`HOME-01` to `HOME-06` still hold on every other address. The same application serves both, and only
requests for the course address reach these routes.

The wording lives in `lang/en/course.php`, so it can be edited without touching the template. The
scenarios deliberately do not quote it. The page is English only for now; Hebrew is one more file,
`lang/he/course.php`, when it is wanted.

## Enrolling

Enrollment here is the first slice of a longer flow. Enroll gets a visitor an account and brings
them back to the course. The goal question, the kavod pledge and the enrollment record come in the
next change, inside this same step, so these scenario IDs stay valid when they arrive. Which sign-in
methods the flow offers is decided in that change as well.

## Wording

The course pages say "Sign in". The tool's top bar keeps "Log in" (`HOME-03`).

## Going to the tool

Links to the tool always leave the course address, using the `course.tool_url` setting. Until the
first rollout the course runs on its own copy of the database, so a case created on the course
address would never reach the real tool.

---

## Scenarios

```gherkin
Feature: Course landing page

  # US-W1
  Scenario: COURSE-01 - The course address serves the landing page            (specified)
    When I open "/" on the course address
    Then I see the course landing page
    When I open "/" on any other address
    Then I see the tool's home page

  # US-W1
  Scenario: COURSE-02 - The page says what the course does for me             (specified)
    When I open the course landing page
    Then the logo is shown
    And the main heading states the promise of the course
    And a supporting line follows it

  # US-W1
  Scenario: COURSE-03 - Enroll is the one primary action                      (specified)
    When I open the course landing page
    Then there is an Enroll link near the top of the page
    And it leads to "/enroll" on the course address

  # US-W1
  Scenario: COURSE-04 - There is a door to the tool                           (specified)
    When I open the course landing page
    Then there is a link to the tool
    And it leads to the tool's address, not the course address

  # US-W1
  Scenario: COURSE-05 - An analyzed case can be seen without an account       (specified)
    Given I am not signed in
    And an example case is configured and it is public
    When I open the course landing page
    Then there is a link to that case on the tool's address
    When the example case is not configured, or is not public
    Then that link leads to the list of public cases instead

  # US-W1
  Scenario: COURSE-06 - The page explains how the course works                (specified)
    When I open the course landing page
    Then I see the three points describing the course experience

  # US-W1
  Scenario: COURSE-07 - The method is explained on the page                   (specified)
    When I open the course landing page
    Then there is an About the method section on the page

  # US-W1
  Scenario: COURSE-08 - The navigation offers About the method and Contact    (specified)
    When I open the course landing page
    Then the navigation links to the About the method section
    And the navigation has a Contact link to the configured email address
    When no contact address is configured
    Then the Contact link is not shown

  # US-W5
  Scenario: COURSE-09 - The top of the page reflects whether I am signed in   (specified)
    Given I am not signed in
    When I open the course landing page
    Then I am offered "Sign in"
    Given I am signed in
    When I open the course landing page
    Then I see my name
    And I am offered a way to sign out

  # US-W1
  Scenario: COURSE-10 - The page fits a phone                                 (specified)
    Given a phone-width screen
    When I open the course landing page
    Then the page does not scroll sideways
    And the Enroll link is visible without scrolling

  # US-W1
  Scenario: COURSE-11 - The video appears only when there is one              (specified)
    Given a landing video address is configured
    When I open the course landing page
    Then the video is shown
    When no landing video address is configured
    Then no video block is shown
    And the rest of the page is unchanged

  # US-W5
  Scenario: COURSE-12 - Signing in from the course returns to the course      (specified)
    Given I am not signed in
    And I am on the course landing page
    When I choose "Sign in"
    And I sign in
    Then I am back on the course landing page

Feature: Enrolling from the course landing page

  # US-W3
  Scenario: ENROLL-01 - Enrolling as a guest creates an account and returns   (specified)
    Given I am not signed in
    When I choose Enroll on the course landing page
    Then I am asked to register
    When I register
    Then I am back on the course landing page, signed in

  # US-W4
  Scenario: ENROLL-02 - Enrolling while signed in does not ask me to sign in  (specified)
    Given I am signed in
    When I choose Enroll on the course landing page
    Then I am not asked to sign in or register
    And I am back on the course landing page with a confirmation
```
