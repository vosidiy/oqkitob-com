# oqkitob-com

`oqkitob-com` is a same-origin Vue SPA with a CodeIgniter 4 API backend and a MySQL database.

The product is organized around user-owned books. Each book has a `type_key` such as `notes`, `todo`, or `finance`, and each type behaves like a small focused app inside the main layout.

## What It Is
A single-page web app where users register, sign in, and create "books".
A book is a mini application workspace. Each book has a type, and each type
maps to dedicated database tables and dedicated frontend/backend behavior.
Money-focused books can also carry a fixed book-level `currency_code`.

## Tech Stack

| Layer     | Technology                        |
|-----------|-----------------------------------|
| Frontend  | Vue 3, Vite, Vue Router, Axios, Vue I18n |
| Backend   | CodeIgniter 4 (JSON API only)     |
| Database  | MySQL                             |
| Auth      | Same-origin cookie sessions       |
| Styling   | `assets/final.min.css` + `assets/custom.css` |

## CSS Library

The project now uses the local `assets/final.min.css` library for basic UI styling.

- Bootstrap CDN is no longer the source of component styles for the app or public pages.
- Use the library's basic component classes for buttons, cards, forms, and alerts.
- Preferred classes in this repo:
  - buttons: `btn`, `btn-primary`, `btn-outline`, `btn-neutral`, `btn-sm`, `btn-lg`
  - cards: `card`, `card-body`
  - forms: `form-label`, `form-control`, `form-select`, `form-check`
  - alerts: `alert`, `alert-danger`, `alert-warning`, `alert-info`, `alert-success`
- Keep styling simple in templates. Avoid adding Bootstrap-only variants such as `btn-dark`, `btn-outline-dark`, `btn-outline-secondary`, `form-control-lg`, or other complex responsive Bootstrap classes when working on these basic surfaces.


## Project Structure

```text
oqkitob-com/
├── api/                                # Public API entry point (renamed CI4 public/ folder)
├── codeigniter/                        # CodeIgniter 4 app, config, models, controllers, tests
├── frontend-src/                       # Vue SPA source
│   ├── public/
│   └── src/
│       ├── api/                        # Axios client + domain API helpers
│       ├── components/                 # Reusable UI components only
│       ├── composables/                # Shared app-side behavior helpers
│       ├── i18n/                       # Locale config, messages, and helpers
│       ├── layouts/                    # AppLayout and GuestLayout
│       ├── router/                     # Vue Router config and guards
│       ├── stores/                     # Auth store + books Pinia store
│       ├── views/                      # Route views and book mini apps
│       │   ├── auth/
│       │   └── book-types/
│       └── main.js
├── dist/                               # Vite build output
├── index.html                          # Root-served SPA entry
├── docs/README.md
├── docs/ARCHITECTURE.md
└── docs/db.sql
```

## Current Frontend Structure

- `frontend-src/src/layouts/AppLayout.vue`
  - authenticated shell for `/home`
  - loads the shared books list for the sidebar
  - hosts the native create-book dialog for the sidebar
  - owns the authenticated language picker
  - remounts the book page when `bookId` changes
- `frontend-src/src/layouts/GuestLayout.vue`
  - minimal guest shell for auth pages
  - owns the guest-page language picker
- `frontend-src/src/views/HomeView.vue`
  - dashboard overview at `/home`
- `frontend-src/src/views/BookView.vue`
  - resolves the selected book and chooses the correct mini app
- `frontend-src/src/views/book-types/*`
  - `NotesApp.vue`
  - `TodoApp.vue`
  - `FinanceApp.vue`
  - `MinishopApp.vue`
  - each child app fetches and renders its own type-specific data
- `frontend-src/src/i18n/*`
  - `index.js`
  - `messages.js`
  - locale bootstrap, persistence, and fallback handling for `uz`, `en`, and `ru`

## Routing

Frontend routes:

- `/app.html` -> redirects to `login`
- `/login` -> guest auth page
- `/register` -> guest scaffold page
- `/forgot-password` -> guest scaffold page
- `/home` -> authenticated dashboard home
- `/home/books/:bookId/:page?` -> authenticated selected-book page

Route guards use `authStore.ensureChecked()`:

- guest users are redirected from `/home` routes to `login`
- authenticated users are redirected away from guest-only routes to `dashboard-home`

## Frontend State

The frontend currently uses a mixed state approach:

- `frontend-src/src/stores/auth.js`
  - custom reactive singleton, not Pinia
  - owns:
    - `state.user`
    - `state.checked`
    - `state.checkPromise`
  - exposes:
    - `checkAuth()`
    - `ensureChecked()`
    - `login(payload)`
    - `logout()`

- `frontend-src/src/stores/books-store.js`
  - Pinia setup store via `useBooksStore()`
  - owns the shared books list only
  - state:
    - `books`
    - `isLoading`
    - `loaded`
    - `errorMessage`
  - methods:
    - `fetchBooks(force = false)`
    - `reset()`

The books store keeps a module-scoped `listPromise` so concurrent sidebar/detail loads reuse the same `/books` request.

## Frontend Localization

The SPA now has a shared `vue-i18n` layer.

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

Behavior:

- authenticated and guest layouts both expose a language picker
- invalid or corrupt stored locale values fall back to English
- missing translation keys also fall back to English
- current scope covers visible frontend UI strings only
- backend-sent error messages are still rendered as returned by the API

## Frontend API Layer

Frontend API helpers live under `frontend-src/src/api/`.

- `client.js`
  - shared Axios instance using relative `/api` URLs
- `errors.js`
  - lightweight helpers for checking `401`, `404`, and backend error messages without importing Axios into every view
- `auth.js`
  - `loginRequest`
  - `logoutRequest`
  - `fetchCurrentUserRequest`
- `books-api.js`
  - `fetchBooksList()`
  - `fetchBookTypes()`
  - `fetchBookById(bookId)`
  - `createBookRequest(payload)`
  - `updateBookRequest(bookId, payload)`
- `minishop.js`
  - minishop products, customers, sales, and payment summary helpers
- `notes.js`
  - `fetchNotes(bookId)`
- `todos.js`
  - `fetchTodos(bookId)`
- `finance.js`
  - `fetchFinanceTransactions(bookId)`

## Book Loading Flow

`BookView.vue` is intentionally compact and self-contained:

1. read `bookId` from the current route
2. try to find the book inside `booksStore.books`
3. if not found, wait for `booksStore.fetchBooks()`
4. try the shared list lookup again
5. if still not found, do a simple fallback request with `fetchBookById(bookId)`
6. render the matching mini app based on `book.type_key`

Each book mini app then fetches its own content on mount:

- notes -> `/api/books/{bookId}/notes`
- todo -> `/api/books/{bookId}/todos`
- finance -> `/api/books/{bookId}/finance`
- minishop -> `/api/books/{bookId}/minishop/*`

The sidebar also owns book creation:

1. open the native create-book dialog from `AppLayout.vue`
2. load active book types from `GET /api/books/types`
3. show only book types first; reveal the rest of the form after a type is chosen
4. if the selected type has `requires_currency = 1`, require a currency choice
5. submit the new book to `POST /api/books`
6. book settings edits use `PUT /api/books/{bookId}` for `title` and `description` only
7. keep `type_key` and `currency_code` immutable after creation
8. validate the returned `book.id`
9. do a full page navigation to `/home/books/{bookId}`

Books list responses are used as the primary frontend metadata source for:

- `title`
- `type_key`
- `currency_code`
- `description`

Book type responses from `GET /api/books/types` now also include:

- `requires_currency`

Currency notes:

- money book types still require a currency during create
- backend accepts custom short codes up to 5 characters
- non-money book types ignore extra submitted currency values

## Session/Auth Notes

- Auth is session-based and same-origin.
- Frontend API requests use relative `/api/...` URLs, so cookies are sent automatically without `withCredentials`.
- Application code uses CodeIgniter sessions, not `$_SESSION` directly.
- Login regenerates the session ID.
- Logout destroys the session.

This is a minimal but reasonable MVP setup for same-origin cookie auth.

## Development Workflow

1. Work in `frontend-src/` for SPA source changes.
2. Run `npm install` in `frontend-src/` if dependencies change.
3. Run `npm run dev` in `frontend-src/` during development.
4. Run `npm run build` in `frontend-src/` before wrapping up frontend refactors.
5. Do not edit compiled files inside `dist/` manually.
6. We don't use database migrations. We manually work with  SQL code using phpMyAdmin interface


## Demo Credentials

From `db.sql`:

- `vosidiy@gmail.com`
- `test@example.com`
- `demo@example.com`
- shared password: `123`

## Reference Files

- [ARCHITECTURE.md](/Applications/MAMP/htdocs/oqkitob-com/docs/ARCHITECTURE.md)
- [DATABASE.md](/Applications/MAMP/htdocs/oqkitob-com/docs/DATABASE.md)
- [schema.sql](/Applications/MAMP/htdocs/oqkitob-com/docs/schema.sql)
- [db.sql](/Applications/MAMP/htdocs/oqkitob-com/docs/db.sql)

## For testing (important)

The repo doesn’t have the local PHPUnit binary installed, and this CodeIgniter setup also doesn’t expose a spark test command here. 
There’s no PHPUnit installed locally or globally, so backend feature execution is blocked by missing test tooling. 
Do other useful verification or check by running PHP syntax checks across the changed backend files to make sure the implementation at least parses cleanly.
