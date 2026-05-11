# Architecture

## Overview

`oqkitob-com` is planned as a same-origin SPA served from the web root, with a CodeIgniter 4 backend exposed under `/api`.

```text
Browser
  -> /
  -> Vue SPA
  -> Axios requests to /api/...
  -> CodeIgniter 4
  -> MySQL
```

This structure is already partially prepared:

- root `index.html` serves the SPA
- root `.htaccess` sends non-API routes to `index.html`
- `api/` acts as the CodeIgniter public entry point
- `codeigniter/` contains the real backend source
- `frontend-src/` contains the Vite/Vue source app

## Current Routing Behavior

Frontend:

- Root `.htaccess` preserves `/api/*`
- All other non-file requests are rewritten to `/index.html`

Backend:

- CodeIgniter base URL is expected to end with `/api/`
- Current test endpoint is `/api/test`


## Recommended Module Boundaries

### Backend modules

- `Auth`
- `Users/Profile`
- `Books`
- `Notes`
- `Todos`
- `Finance`

Recommended backend layering:

- Controllers: request/response only
- Services: business rules
- Models: persistence
- Validation: request rules


## Authentication Strategy

Use session-based authentication for the web SPA.

Implementation direction:

- Browser logs in through `/api/auth/login`
- Backend validates credentials
- CodeIgniter creates a server-side session
- Session is stored in MySQL
- Browser keeps the session cookie
- SPA calls `/api/auth/me` on refresh to restore auth state

Why this fits the project:

- The SPA and API live on the same domain/path family
- Browser cookie auth is straightforward for this setup
- Logout and forced session invalidation are simpler than JWT-based browser auth

## Mobile consideration

For the future mobile app:

- Keep web auth session-based
- Add token auth later for mobile clients
- Do not force the web SPA into JWT now just because mobile may need tokens later

## Database Session Recommendation

Use CodeIgniter's database session handler with a `ci_sessions` table.

Benefits:

- No file-based session dependency
- Easier scaling later
- Cleaner hosting behavior on shared environments

## Security Recommendations

- Use `password_hash()` and `password_verify()` through PHP best practices
- Keep explicit routes instead of relying on broad auto-routing
- Validate authorization by book ownership on every book-specific endpoint
- Store timestamps in UTC
- Use server-side validation for all incoming write requests
- Keep CSRF protection in mind for session-authenticated web endpoints
- Use HTTPS in production

## Book-Centric Data Model

The central domain rule is:

- A user owns books
- A book has one `type_key`
- Every feature record belongs to a single `book_id`

This avoids mixing unrelated feature data and keeps each mini app isolated.

Example:

```text
users
  -> books
      -> notes
      -> todos
      -> finance_categories
      -> finance_transactions
```

## Recommended API Style

- JSON in / JSON out
- Standard status codes
- Flat, predictable response envelopes

Suggested endpoint families:

```text
/api/auth/*
/api/books/*
/api/books/{bookId}/notes/*
/api/books/{bookId}/todos/*
/api/books/{bookId}/finance/*
```

## Recommended Deployment Shape

Planned cPanel-compatible structure:

```text
public_html/
├── index.html
├── assets/
├── api/
└── codeigniter/
```

Notes:

- `index.html` and `assets/` come from the Vue build
- `api/` remains the public API entrypoint
- `codeigniter/writable/` must stay writable on hosting

## Technical Conventions

- Frontend API calls should use relative URLs such as `/api/books`
- Avoid hardcoding full domains inside frontend code
- Keep table names plural and snake_case
- Keep API resources scoped through `book_id` where applicable
- Prefer soft deletes for user-owned business records


## Architecture Summary

The best near-term structure is:

- Vue SPA at `/`
- CodeIgniter API at `/api`
- Web auth through database-backed sessions
- UUID-based core entities
- Central `books` table with type-specific feature tables
