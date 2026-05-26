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

The current architecture is intentionally book-centric: a user owns books, each book has one `type_key`, and each type behaves like a small app whose data is isolated by `book_id`.

## Frontend Composition

- `App.vue`
  - renders the router only
- `main.js`
  - mounts Vue
  - registers Pinia
  - registers Vue Router
  - registers Vue I18n
  - runs the initial `authStore.checkAuth()`

- `i18n/index.js`
  - creates the shared `vue-i18n` instance
  - defaults to `uz`
  - falls back to `en`
  - persists locale selection in `localStorage`

- `i18n/messages.js`
  - holds the shared message catalog for `uz`, `en`, and `ru`

### Layouts

- `AppLayout.vue`
  - authenticated shell for `/home`
  - renders:
    - signed-in user summary
    - shared books sidebar
    - create-book dialog
    - authenticated language picker
    - logout action
    - child route content
  - warms the shared books list with `booksStore.fetchBooks()`
  - keys the child `RouterView` by `bookId` so each selected book remounts as a fresh mini app

- `GuestLayout.vue`
  - lightweight shell for `/login`, `/register`, and `/forgot-password`
  - exposes the guest-side language picker so auth pages stay localized before sign-in

### Route Views

- `HomeView.vue`
  - authenticated overview page at `/home`
- `BookView.vue`
  - selected-book controller view at `/home/books/:bookId`
  - resolves selected-book metadata
  - chooses the correct mini app component

### Book Mini Apps

Book-specific UI no longer lives in `components/`. It now lives in `views/book-types/`.

- `views/book-types/notes/NotesApp.vue`
- `views/book-types/todo/TodoApp.vue`
- `views/book-types/finance/FinanceApp.vue`
- `views/book-types/minishop/MinishopApp.vue`
- `views/book-types/minishop/*`
  - `MainTab.vue`
  - `SalesTab.vue`
  - `CustomersTab.vue`
  - `ReportsTab.vue`

Each mini app:

- accepts a `book` prop
- fetches its own type-specific data on mount
- owns its own loading and error UI
- redirects to `login` on `401`

## Frontend State Architecture

The app currently uses a mixed state model.

### Auth Store

`frontend-src/src/stores/auth.js` is a custom reactive singleton, not Pinia.

Responsibilities:

- hold the authenticated user
- dedupe `/auth/me` checks through `state.checkPromise`
- provide router-safe auth bootstrap through `ensureChecked()`
- handle login/logout state updates

State shape:

- `state.user`
- `state.checked`
- `state.checkPromise`

Public methods:

- `checkAuth()`
- `ensureChecked()`
- `login(payload)`
- `logout()`

### Books Store

`frontend-src/src/stores/books-store.js` is a Pinia setup store via `useBooksStore()`.

Responsibilities:

- own the shared books list used by the sidebar
- expose list-level loading/error state
- dedupe concurrent `/books` requests with a module-scoped `listPromise`

State:

- `books`
- `isLoading`
- `loaded`
- `errorMessage`

Public methods:

- `fetchBooks(force = false)`
- `reset()`

This store intentionally does not own selected-book state anymore. Selected-book resolution now lives inside `BookView.vue`.

## Routing and Guards

`frontend-src/src/router/index.js` defines three route groups:

- redirect
  - `/app.html` -> `login`
- guest-only under `GuestLayout`
  - `/login`
  - `/register`
  - `/forgot-password`
- authenticated under `AppLayout`
  - `/home`
  - `/home/books/:bookId/:page?`

The global `beforeEach` guard uses `authStore.ensureChecked()`:

- `requiresAuth` routes redirect guests to `login`
- `requiresGuest` routes redirect signed-in users to `dashboard-home`

## Frontend Data Flow

### Shared Book Metadata

The main metadata source is `GET /api/books`.

`AppLayout.vue` fetches this list once for the sidebar. `BookView.vue` reuses the same shared list first before attempting any fallback request.

### Book Creation Flow

`AppLayout.vue` also owns the sidebar create flow:

1. open the native `<dialog>`
2. load active types from `GET /api/books/types`
3. submit `title`, `description`, and `type_key` to `POST /api/books`
4. validate the returned `book.id`
5. do a full page navigation to `/home/books/:bookId`

### Selected Book Resolution

`BookView.vue` uses this sequence:

1. read `bookId` from the route
2. search `booksStore.books`
3. if missing, await `booksStore.fetchBooks()`
4. search the shared list again
5. if still missing, call `fetchBookById(bookId)` as a simple fallback
6. resolve the child app from `book.type_key`

This keeps the common path simple while still allowing direct URL access when the shared list is initially empty.

### Type-Specific Data

After the selected book is known:

- `NotesApp.vue` calls `fetchNotes(book.id)`
- `TodoApp.vue` calls `fetchTodos(book.id)`
- `FinanceApp.vue` calls `fetchFinanceTransactions(book.id)`
- `MinishopApp.vue` calls minishop product/category/customer/sales helpers under `api/minishop.js`

Because `AppLayout` keys `RouterView` by `bookId`, switching between books remounts `BookView` and the child mini app, which resets filters, dialogs, and local in-memory state cleanly.

## Frontend Localization Architecture

The frontend now uses a shared `vue-i18n` layer instead of component-local string literals.

- supported locales:
  - `uz`
  - `en`
  - `ru`
- default locale:
  - `uz`
- fallback locale:
  - `en`
- persisted locale key:
  - `oqkitob_locale`

Current behavior:

- `AppLayout.vue` and `GuestLayout.vue` both bind to the same locale state
- locale changes apply immediately across routed views
- unsupported locale values are coerced to English
- missing keys resolve through English fallback
- helper functions in `i18n/helpers.js` translate app-owned dynamic labels such as:
  - book type names
  - todo priorities
  - payment statuses

Scope note:

- only visible frontend UI strings are localized right now
- backend-provided validation and error messages are not localized yet

## Frontend API Layer

API helpers live in `frontend-src/src/api/`.

- `client.js`
  - shared Axios instance with `baseURL: '/api'`
- `errors.js`
  - small helpers for reading response status and backend messages from unknown thrown errors
- `auth.js`
  - auth request helpers
- `books-api.js`
  - books list, book-type loading, single-book fallback, and book creation helpers
- `notes.js`, `todos.js`, `finance.js`
  - book-type request helpers
- `minishop.js`
  - minishop products, customers, sales, and payment summary helpers

This keeps transport code out of views and stores while staying lightweight.

## Backend Module Boundaries

- `Auth`
  - `Api\AuthController`
  - login, logout, current user
- `Books`
  - `Api\BooksController`
  - authenticated books list
  - active book-type list
  - book creation
  - single-book metadata fallback
- `Notes`
  - `Api\NotesController`
  - notes-book content
- `Todos`
  - `Api\TodosController`
  - todo-book content
- `Finance`
  - `Api\FinanceController`
  - finance-book transactions
- `Minishop`
  - minishop products, customers, sales, receipts, and payment summary endpoints
- Shared authenticated API layer
  - `AuthenticatedApiController`
  - `BookAccessService`

Recommended layering:

- controllers: request/response orchestration
- services: ownership and access rules
- models: query and persistence logic

## API Surface

Current routes:

```text
/api/auth/login
/api/auth/logout
/api/auth/me
/api/books      (GET list)
/api/books      (POST create)
/api/books/types (GET active types)
/api/books/{bookId} (GET metadata fallback)
/api/books/{bookId} (PUT update title/description)
/api/books/{bookId}/notes
/api/books/{bookId}/todos
/api/books/{bookId}/finance
/api/books/{bookId}/minishop/products
/api/books/{bookId}/minishop/customers
/api/books/{bookId}/minishop/sales
```

Conventions:

- book-type routes use `/api/books/{bookId}/{type}`
- finance uses `/finance` while the response payload remains `transactions`
- minishop uses `/minishop/...` subroutes for tab-specific workflows
- book-type endpoints validate:
  - authenticated user
  - book ownership
  - active/non-deleted book
  - matching `type_key`

## Session/Auth Architecture

- Browser auth is session-based and same-origin.
- CodeIgniter owns the server-side session lifecycle.
- App code uses the CI4 session service, not direct `$_SESSION` access.
- `BaseController` preloads the session service.
- Auth-protected routes use the `auth` filter.
- `AuthenticatedApiController` provides helpers for reading the authenticated user ID.
- Read-only authenticated endpoints should release the session lock early with `$this->session->close()` after reading `user_id`.
- Login regenerates the session ID.
- Logout destroys the session.

This is a good fit for the current MVP because the SPA and API share one origin and the browser sends the session cookie automatically on relative `/api` calls.

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

Rules:

- a user owns books
- a book has exactly one `type_key`
- a book may also have one immutable `currency_code`
- book creation may require `currency_code` for money-focused types, but the format is not limited to a fixed ISO allowlist
- type-specific records belong to one `book_id`
- type-specific data stays isolated in dedicated tables
- UUID strings are used for core entities

## Current Tradeoffs

- auth is still a custom reactive store while books use Pinia
- `BookView` owns selected-book resolution locally for readability
- the frontend keeps `GET /api/books/{bookId}` only as a fallback path
- `/register` and `/forgot-password` exist as frontend scaffolds and are not fully wired to backend flows yet

These tradeoffs are intentional for MVP simplicity and can be evolved later without changing the core book-centric structure.
