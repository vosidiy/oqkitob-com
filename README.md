# oqkitob-com

`oqkitob-com` is a Vue.js single-page application with a CodeIgniter 4 API backend and a MySQL database.

The core product idea is a books platform where each user owns multiple mini software workspaces called books. Each book has a `type_key` such as `notes`, `todo`, or `finance`, and each type stores its data in dedicated tables.

## Project Structure

```text
oqkitob-com/
├── api/                                # Public API entry point (renamed CI4 public/ folder)
├── codeigniter/                        # CodeIgniter 4 app, config, models, controllers, tests
├── frontend-src/                       # Vue SPA source only
│   └── src/
│       ├── components/books/           # Dedicated book-type render components
│       ├── layouts/                    # Shared authenticated shell layouts
│       ├── router/                     # Vue Router
│       ├── services/                   # Axios / API helpers
│       ├── stores/                     # Shared frontend state
│       └── views/                      # Route-level views
├── dist/                               # Vite build output only
├── index.html                          # Root-served SPA entry for local/published runtime
├── about.html
├── contact.html
├── terms.html
├── README.md
├── ARCHITECTURE.md
└── db.sql
```

## Current Runtime

- Frontend SPA routes:
  - `/` -> public landing/login page
  - `/home` -> authenticated shell + account overview
  - `/home/books/:bookId` -> authenticated shell + selected book content
- Backend API routes:
  - `POST /api/auth/login`
  - `POST /api/auth/logout`
  - `GET /api/auth/me`
  - `GET /api/books`
  - `GET /api/books/{bookId}/notes`
  - `GET /api/books/{bookId}/todos`
  - `GET /api/books/{bookId}/finance`
- Frontend API calls are same-origin and relative, for example `/api/books`.
- Root `.htaccess` keeps `/api/*` on the backend and falls back to `/index.html` for SPA routes.

## Current Frontend Behavior

- The authenticated shell lives in `frontend-src/src/layouts/BaseShell.vue`.
- The shell keeps a persistent left sidebar with:
  - signed-in user name and email
  - clickable list of the user’s visible books
  - active-book highlighting based on the route param
- When no book is selected, the right panel shows the account overview view.
- When a book is selected, the route-level container `BookContentView.vue` loads the correct dataset and renders a dedicated component for that book type:
  - `NotesBookContent.vue`
  - `TodoBookContent.vue`
  - `FinanceBookContent.vue`

## Current Backend Behavior

- `BooksController` owns only the authenticated sidebar book list.
- `NotesController`, `TodosController`, and `FinanceController` own type-specific book content endpoints.
- `AuthenticatedApiController` centralizes authenticated API helpers such as reading the current `user_id`.
- `BookAccessService` centralizes “user owns active book” lookup so controllers do not duplicate ownership checks.

## Session/Auth Rules

- Web auth uses same-origin cookie sessions backed by the `ci_sessions` table.
- Application code must use CodeIgniter’s session service only.
- Do not read from `$_SESSION` directly in app code.
- Do not call `$session->start()` in controllers or filters.
- `BaseController` preloads the CI4 session service once for controller access.
- Read-only authenticated endpoints should read `user_id` and then call `$this->session->close()` before database work to reduce session-lock contention for concurrent SPA requests.

## Domain Rules

- A user owns books.
- A book has exactly one `type_key`.
- Book-type records always belong to a single `book_id`.
- Book-type endpoints must validate both ownership and expected book type.
- Keep book-type data isolated in dedicated tables such as `notes`, `todos`, and `finance_transactions`.

## Demo Credentials

From `db.sql`:

- `ali@example.com`
- `malika@example.com`
- `jasur@example.com`
- Shared password: `Demo123!`

## Development Workflow

1. Edit Vue SPA source in `frontend-src/`.
2. Run `npm install` in `frontend-src/` if needed.
3. Run `npm run dev` in `frontend-src/` for local development.
4. Run `npm run build` in `frontend-src/` to generate `dist/`.
5. Manually copy `dist/index.html`, `dist/assets/`, and `dist/favicon.ico` into the repository root when validating the root-served SPA.
6. Do not hand-edit compiled files inside `dist/`.

## Current Coding Conventions

- Keep frontend source only in `frontend-src/`.
- Keep backend source only in `api/` and `codeigniter/`.
- Use Bootstrap classes only for current authenticated UI work unless a later task introduces a broader design system.
- Keep backend endpoints under `/api/*`.
- Prefer explicit controller/module boundaries over catch-all controllers.
- Keep table names plural and snake_case.
- Prefer soft deletes for user-owned business records.

## Reference Files

- [ARCHITECTURE.md](/Applications/MAMP/htdocs/oqkitob-com/ARCHITECTURE.md)
- [DATABASE.md](/Applications/MAMP/htdocs/oqkitob-com/DATABASE.md)
- [schema.sql](/Applications/MAMP/htdocs/oqkitob-com/schema.sql)
- [db.sql](/Applications/MAMP/htdocs/oqkitob-com/db.sql)
