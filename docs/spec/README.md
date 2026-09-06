# Functional specification

This describes what LearnGemara **does today**, written before the Stage 0 refactors change any of
it. It is a baseline: the behavior recorded here is what must still be true afterwards.

It is not a design document and not a wish list. Where the current behavior is wrong, that is
recorded as a defect rather than quietly corrected (see *Defects* below).

## How to read it

Each domain file pairs **prose** — what the user is trying to do, and why the behavior is shaped
the way it is — with **Gherkin scenarios**, which are the precise, checkable form. The prose exists
because a list of scenarios does not explain intent, and intent is what tells you whether a future
change is a fix or a regression.

## Scenario IDs

Every scenario has a stable ID: `DIAG-07`, `LIST-04`, `SAVE-11`. IDs are permanent — if a scenario
is removed, its ID is retired rather than reused, so a reference in an old commit still resolves.

Tests name the ID they cover, in the test name or a comment:

```php
#[Test]
public function save_persists_all_input_conditions(): void   // SAVE-03
```

```js
test('arrows stay connected after resize', async ({ page }) => {   // DIAG-12
```

This makes completeness checkable rather than asserted: any ID with no test referencing it is a
specified behavior nothing verifies.

## Defects

A spec derived from current behavior will faithfully encode current bugs unless it says otherwise.
Scenarios describing behavior that is **wrong** are tagged `@defect` and state the intended
behavior, not the observed one. Their tests are expected to fail until fixed.

```gherkin
@defect
Scenario: DOMAIN-NN - The behavior as it should be
```

(Illustrative IDs in this file use the placeholder `DOMAIN-NN` so they cannot be
mistaken for real scenarios.)

Untagged scenarios describe behavior that is correct and must be preserved.

## Provenance

Scenarios are marked with how they were established:

- **(observed)** — verified by driving the running application. Layout, geometry, and timing
  behavior is always observed, never inferred, because the code says what was intended and the
  browser says what happens.
- **(code)** — read from the implementation, for logic with no visible surface.

## Domains

| File | Covers | Scenarios |
|---|---|---|
| [`landing.md`](landing.md) | Home page, navigation, entry points | HOME |
| [`language.md`](language.md) | Toggle, cookie persistence, RTL | LANG |
| [`reference-data.md`](reference-data.md) | `/api/tractates`, masechet and daf pickers, gematriya | REF |
| [`case-authoring.md`](case-authoring.md) | Progressive reveal cascade, Sefaria amud lookup | CASE |
| [`input-conditions.md`](input-conditions.md) | Value vs draft vs Not Relevant, reset | COND |
| [`diagram.md`](diagram.md) | Ovals, act, din bar, arrows, icons, `?hideicons` | DIAG |
| [`saving.md`](saving.md) | Auth gate, create vs update, save-as, validation | SAVE |
| [`viewing.md`](viewing.md) | Show, edit, read-only rendering | VIEW |
| [`case-list.md`](case-list.md) | Filters, search, ordering, pagination, delete | LIST |
| [`authorization.md`](authorization.md) | Ownership rules across every action | AUTHZ |
| [`auth.md`](auth.md) | Login, register, logout, verification, reset, OAuth | AUTH |

All eleven domains are written.
