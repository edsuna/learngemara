# Authorization

There is no policy class and no gate. Authorization lives in three unrelated places, which is why
it has been inconsistent:

1. **Route middleware** — `routes/web.php` splits the resource so `create`, `index` and `show` are
   public while `store`, `update`, `edit` and `destroy` require `auth`.
2. **`GemaraCaseRequest::authorize()`** — if the request body carries a `caseId`, that case must
   exist and belong to the current user. This is what protects updates.
3. **`GemaraCases::removeGemaraCase()`** — an explicit ownership check before deleting.

Nothing else enforces ownership. In particular **hiding a control in Blade is not authorization**,
and that mistake has already produced one real vulnerability here.

## The delete hole (fixed)

`removeGemaraCase($id)` previously called `GemaraCase::destroy($id)` with no check of any kind. The
only thing standing between a visitor and every case in the database was a Blade `@if` hiding the
button.

Livewire actions are callable by anyone who can render the component, and the **public case list
renders for unauthenticated visitors**. Verified against the real component: both a logged-in
non-owner and an anonymous guest could delete any case by id, private ones included.
`GemaraCaseController::destroy()` is an empty stub, so this action was the only delete path in the
application and the only place a check could live.

It now loads the case and aborts 403 unless the current user owns it. AUTHZ-04 through AUTHZ-07
exist to keep it that way.

## Ownership is the only dimension

There are no roles, no admin, no sharing grants. A case has exactly one `user_id`, and every write
requires being that user. `public` is a visibility flag on *listing*, not a permission — see
VIEW-09.

---

## Scenarios

```gherkin
Feature: Authorization

  Scenario: AUTHZ-01 - Writes require authentication                          (code)
    When a guest POSTs a case
    Then they receive 401

  Scenario: AUTHZ-02 - Editing requires authentication                        (observed)
    When a guest opens "/gemara_cases/{id}/edit"
    Then they are redirected to the login page

  Scenario: AUTHZ-03 - A user cannot update someone else's case               (code)
    Given a case owned by another user
    When I PUT changes to it
    Then I receive 403
    And the case is unchanged

  Scenario: AUTHZ-04 - A guest cannot delete a case                           (code)
    Given a public case owned by someone
    When an unauthenticated visitor calls the delete action with its id
    Then they receive 403
    And the case still exists

  Scenario: AUTHZ-05 - A user cannot delete someone else's case               (code)
    Given a case owned by another user
    When I call the delete action with its id
    Then I receive 403
    And the case still exists

  Scenario: AUTHZ-06 - An owner can delete their own case                     (observed)
    Given a case I own
    When I confirm deletion
    Then the case is removed

  Scenario: AUTHZ-07 - Deleting a nonexistent case is forbidden, not an error (code)
    When I call the delete action with an id that does not exist
    Then I receive 403
    # not a 500, and not a silent success

  Scenario: AUTHZ-08 - Reading a case does not require authentication         (observed)
    Given a public case
    When a guest opens it
    Then they receive it
    # deliberate: cases are meant to be shareable
```

## Visibility is not a permission

`public` is a listing flag, not an access control. An unlisted case is served in full to anyone
holding its URL, guests included — deliberately, so that a case can be shared as a link without
being published to the public list. See `viewing.md` VIEW-09.

## Note

`GemaraCaseController::destroy()` is an empty method body. The `DELETE /gemara_cases/{id}` route
exists and is auth-gated, so calling it returns a success response having done nothing. It is not
a security problem — it deletes nothing — but it is a trap for anyone who assumes the REST route
works.
