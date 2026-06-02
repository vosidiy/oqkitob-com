# Project Plan

## Goal

Build `oqkitob-com` as a single-page web app where users register, sign in, and create "books". A book is a mini application workspace. Each book has a type, and each type maps to dedicated database tables and dedicated frontend/backend behavior.

Initial book types planned for MVP:

- `notes`
- `finance`

Future book types:

- `sales`
- other business or personal productivity modules

## Current Repository State

The repository is already prepared with a split frontend/backend structure:

```text
api/                  CodeIgniter public entry point
codeigniter/          Backend application and framework files
frontend-src/         Vue/Vite source code
index.html            Root SPA file
.htaccess             SPA routing rewrite
```

Observed current implementation:

- `frontend-src/src/App.vue` contains a Vue-to-CodeIgniter connectivity test.
- `frontend-src/vite.config.js` proxies `/api` to `http://localhost:8888`.
- `codeigniter/app/Config/Routes.php` currently defines `/api/test`.
- `codeigniter/app/Controllers/Api/TestController.php` returns a JSON test payload.

## Product Model

Core idea:

- A user owns many books.
- Each book has exactly one type.
- The book type determines which UI opens and which tables are used.

Examples:

- `notes` book -> data stored in `app_notes` table
- `finance` book -> data stored in `app_finance_categories`, `app_finance_transactions` tables

## Architecture Decisions

### 1. Frontend

- Vue 3 SPA
- Axios for API requests
- Build output should ultimately replace root `index.html` and create root `assets/`
- Bootstrap classes only for the first UI phase

### 2. Backend

- CodeIgniter 4 used as JSON API backend
- Explicit routes only
- Controllers should stay thin
- Business logic should move into services later
- Validation should happen server-side for every write endpoint

### 3. Authentication

Recommended web auth approach:

- Same-origin cookie session auth
- Session data stored in MySQL via CodeIgniter database session handler
- Passwords stored only as hashes
- Email verification and password reset codes stored in dedicated tables, not directly in the `users` table

Why this is recommended:

- It matches the current same-domain SPA + `/api` backend structure
- It is simpler and safer for browser-based auth than JWT for the web SPA
- It keeps logout/session invalidation easier to manage

Future mobile app direction:

- Keep web using sessions
- Add token-based auth later for mobile clients when the mobile app starts

### 4. IDs

Recommended MVP choice:

- Use UUID strings in `CHAR(36)` columns for main entities

Reason:

- Easy to inspect manually in phpMyAdmin
- Easy to move data across environments
- Lower implementation complexity than binary UUID storage


## Recommended MVP Scope

### Phase 1: Foundation

- User registration
- User login/logout
- Current authenticated user endpoint
- Books CRUD
- Default book selection
- Route and folder conventions for SPA and API

### Phase 2: Notes Book

- Create notes book
- List notes
- Create/update/delete note
- Optional pinning and ordering

### Phase 3: Finance Book

- Create finance book
- Manage finance categories
- Add income/expense transactions
- Monthly summaries later

### Phase 4: Hardening

- Email verification
- Password reset
- Activity logging
- Better settings/preferences

Note:

- The existing `/api/test` route can remain temporarily for connectivity checks.


## Risks To Avoid Early

- Mixing all book data in one generic table
- Storing reset codes and verification codes directly in user rows forever
- Relying only on frontend validation
- Allowing feature tables to exist without a `book_id`

## Success Criteria For Next Implementation Stage

- A new user can register and log in
- A logged-in user can create books
- A book can open the correct frontend page by type
- Notes/Todo/Finance tables are already ready to receive data
- Database structure stays clean as new book types are added
