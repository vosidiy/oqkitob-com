# Architecture

## Overview

`oqkitob-com` is a same-origin SPA served from the web root, with a CodeIgniter 4 backend exposed under `/api`.

```text
Browser
  -> /
  -> Vue SPA
  -> /api/...
  -> CodeIgniter 4
  -> MySQL
```

The near-term architecture is intentionally book-centric: a user owns books, each book has one `type_key`, and each mini app keeps its own data isolated by `book_id`.

## Frontend Composition

- `App.vue` renders the router only.
- `/home` uses `BaseShell.vue` as the authenticated layout shell.
- The shell owns:
  - user summary in the sidebar
  - shared book navigation
  - logout action
  - nested route rendering
- Child route views:
  - `DashboardHomeView.vue` for the no-book overview state
  - `BookContentView.vue` as the route-level selected-book container
- `BookContentView.vue` loads the selected dataset and renders one dedicated book-type component from `components/books/`.

## Backend Module Boundaries

- `Auth`
  - `AuthController`
  - login, logout, current user profile
- `Books`
  - `BooksController`
  - sidebar book list / book metadata for the authenticated user
- `Notes`
  - `NotesController`
  - notes data for a `notes` book
- `Todos`
  - `TodosController`
  - todo data for a `todo` book
- `Finance`
  - `FinanceController`
  - finance transactions for a `finance` book
- Shared authenticated API layer
  - `AuthenticatedApiController`
  - `BookAccessService`

Recommended layering:

- Controllers: request/response only
- Services: ownership and access rules
- Models: persistence and query shape

## Session/Auth Architecture

- Browser auth is session-based and same-origin.
- CodeIgniter owns the server-side session lifecycle.
- Application code uses the CI4 session service only.
- `BaseController` preloads the shared session service for controllers.
- `AuthFilter` checks `user_id` through the session service and returns `401` for guests.
- `AuthenticatedApiController` provides `currentUserId()` helpers for authenticated API controllers.
- Read-only authenticated endpoints should release the session lock early with `$this->session->close()` after reading the authenticated user ID.

This approach is preferred over direct `$_SESSION` usage because it stays aligned with the framework, avoids duplicate session boot logic, and scales better for concurrent SPA requests.

## API Surface

Current authenticated API families:

```text
/api/auth/login
/api/auth/logout
/api/auth/me
/api/books
/api/books/{bookId}/notes
/api/books/{bookId}/todos
/api/books/{bookId}/finance
```

Conventions:

- Book-type endpoints use `/api/books/{bookId}/{type}`.
- Finance uses `/finance` even though the response payload key remains `transactions`.
- Every book-type endpoint validates:
  - authenticated user
  - book ownership
  - book is active / not archived / not deleted
  - expected `type_key`

## Data Model

Core hierarchy:

```text
users
  -> books
      -> notes
      -> todos
      -> finance_transactions
      -> finance_categories
```

Important rules:

- Core entities use UUID strings.
- Tables remain plural and snake_case.
- Timestamps are stored in UTC.
- Type-specific records must never mix across books.

## Performance Notes

- Session-backed auth is appropriate because the SPA and API share the same origin.
- Database-backed sessions avoid file-session hosting issues and scale more cleanly later.
- Releasing the session lock on read endpoints improves parallel SPA loading behavior.
- Dedicated controllers per book type keep route handling smaller and prevent `BooksController` from becoming a catch-all module.

## Technical Conventions

- Use relative frontend API URLs such as `/api/books`.
- Avoid hardcoded domains in frontend source.
- Use CodeIgniter session service only.
- Do not call `$session->start()` in app controllers or filters.
- Do not read `$_SESSION` directly in app code.
- Keep Vue layouts in `frontend-src/src/layouts/`.
- Keep reusable book-type UI in `frontend-src/src/components/books/`.
