# oqkitob-com

`oqkitob-com` is a same-origin Vue SPA with a CodeIgniter 4 API backend and a MySQL database.

The product is organized around user-owned books. Each book has a `type_key` such as `notes`, `todo`, or `finance`, and each type behaves like a small focused app inside the main dashboard.

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
│       ├── layouts/                    # AppLayout and GuestLayout
│       ├── router/                     # Vue Router config and guards
│       ├── stores/                     # Auth store + books Pinia store
│       ├── views/                      # Route views and book mini apps
│       │   ├── auth/
│       │   └── book-types/
│       └── main.js
├── dist/                               # Vite build output
├── index.html                          # Root-served SPA entry
├── README.md
├── ARCHITECTURE.md
└── db.sql
```

## Current Frontend Structure

- `frontend-src/src/layouts/AppLayout.vue`
  - authenticated shell for `/home`
  - loads the shared books list for the sidebar
  - remounts the book page when `bookId` changes
- `frontend-src/src/layouts/GuestLayout.vue`
  - minimal guest shell for auth pages
- `frontend-src/src/views/LandingPage.vue`
  - public landing page at `/`
- `frontend-src/src/views/HomeView.vue`
  - dashboard overview at `/home`
- `frontend-src/src/views/BookView.vue`
  - resolves the selected book and chooses the correct mini app
- `frontend-src/src/views/book-types/*`
  - `NotesApp.vue`
  - `TodoApp.vue`
  - `FinanceApp.vue`
  - each child app fetches and renders its own type-specific data

## Routing

Frontend routes:

- `/` -> public landing page
- `/login` -> guest auth page
- `/register` -> guest scaffold page
- `/forgot-password` -> guest scaffold page
- `/home` -> authenticated dashboard home
- `/home/books/:bookId` -> authenticated selected-book page

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

- `frontend-src/src/stores/books.js`
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
- `books.js`
  - `fetchBooksList()`
  - `fetchBookById(bookId)`
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

## Backend API Surface

Current API routes:

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`
- `GET /api/books`
- `GET /api/books/{bookId}`
- `GET /api/books/{bookId}/notes`
- `GET /api/books/{bookId}/todos`
- `GET /api/books/{bookId}/finance`

Books list responses are used as the primary frontend metadata source for:

- `title`
- `type_key`
- `description`

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

## Demo Credentials

From `db.sql`:

- `ali@example.com`
- `malika@example.com`
- `jasur@example.com`
- shared password: `Demo123!`

## Reference Files

- [ARCHITECTURE.md](/Applications/MAMP/htdocs/oqkitob-com/ARCHITECTURE.md)
- [DATABASE.md](/Applications/MAMP/htdocs/oqkitob-com/DATABASE.md)
- [schema.sql](/Applications/MAMP/htdocs/oqkitob-com/schema.sql)
- [db.sql](/Applications/MAMP/htdocs/oqkitob-com/db.sql)
