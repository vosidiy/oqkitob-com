# oqkitob-com

`oqkitob-com` is planned as a Vue.js single-page application with a CodeIgniter 4 API backend and a MySQL database.

The product idea is a "books" platform where each user creates mini software workspaces called books. Each book has a type such as `notes`, `todo`, or `finance`, and each type stores data in its own set of tables.

## Project Structure

```text
oqkitob-com/
├── api/                 # Public API entry point (renamed CI4 public/ folder)
│   └── index.php
├── codeigniter/         # CodeIgniter 4 application, system, writable, tests
│   ├── app/
│   ├── system/
│   └── writable/
├── frontend-src/        # Frontend source code only
│   ├── src/             # Vue SPA source
│   ├── public/          # Vite public assets only
│   ├── index.html
│   ├── package.json
│   └── vite.config.js
├── dist/                # Vite-generated SPA build output
│   ├── index.html
│   └── assets/
├── index.html           # Copied SPA entry for local root testing
├── about.html           # Editable SEO page source
├── contact.html         # Editable SEO page source
├── terms.html           # Editable SEO page source
├── .htaccess            # Root SPA rewrite for published SPA builds
├── .gitattributes
└── .gitignore
```

## Source of Truth

- Vue SPA code is written in `frontend-src/src/`.
- Root `about.html`, `contact.html`, and `terms.html` are the editable source for SEO/static content.
- Backend code lives only in `api/` and `codeigniter/`.
- `dist/` contains Vite-generated SPA output only.
- The repository root intentionally contains both editable SEO pages and copied SPA runtime files.

## Current Technical Behavior

- Frontend source lives in `frontend-src/`.
- Vite builds the SPA into the root-level `dist/` folder.
- Vue uses Axios and calls the backend through relative `/api/...` URLs.
- Vite dev proxy forwards `/api` requests to `http://localhost:8888`.
- CodeIgniter currently exposes a test endpoint at `/api/test`.
- Root `.htaccess` serves real root files directly, while allowing `/api/*` to pass through and falling back to `/index.html` for SPA routes.

## Planned Runtime Layout

Local:

```text
http://localhost:8888/        -> Current site root / eventual published SPA
http://localhost:8888/api/    -> CodeIgniter API
```

Development artifact:

```text
dist/
├── index.html
├── favicon.ico
└── assets/
```

Published deployment target:

```text
public_html/
├── index.html      # copied from dist when publishing
├── assets/         # copied from dist when publishing
├── favicon.ico     # copied from dist when publishing
├── about.html      # maintained in root as static source
├── contact.html    # maintained in root as static source
├── terms.html      # maintained in root as static source
├── api/
└── codeigniter/
```

## Planning Files

- [PROJECT_PLAN.md](/Applications/MAMP/htdocs/oqkitob-com/PROJECT_PLAN.md)
- [ARCHITECTURE.md](/Applications/MAMP/htdocs/oqkitob-com/ARCHITECTURE.md)
- [DATABASE.md](/Applications/MAMP/htdocs/oqkitob-com/DATABASE.md)
- [schema.sql](/Applications/MAMP/htdocs/oqkitob-com/schema.sql)

## Recommended Direction

- Keep the web app as same-origin SPA + cookie session auth.
- Store web sessions in MySQL using CodeIgniter's database session handler.
- Use application-generated UUID strings (`CHAR(36)`) for primary entities in the MVP.
- Start API routes under `/api/...` For example:  `/api/test`.
- Keep book-type data in dedicated tables linked through a central `books` table.

## Frontend Development Workflow

1. Edit frontend source in `frontend-src/`.
2. Install dependencies in `frontend-src/` with `npm install`.
3. Run `npm run dev` for the Vite development server.
4. Run `npm run build` to generate the SPA files in `dist/`.
5. Manually copy `dist/index.html`, `dist/assets/`, and `dist/favicon.ico` into the repository root when testing the root-served SPA.
6. Edit `about.html`, `contact.html`, and `terms.html` directly in the repository root as needed.
7. Use `npm run preview` to inspect the built SPA when needed.

## Build and Publish Workflow

- Day-to-day frontend work happens only in `frontend-src/`.
- `npm run build` writes the compiled SPA to `dist/`.
- `dist/` is used to verify and stage only the SPA build output.
- The repository root keeps editable static pages plus copied SPA runtime files for local root-based testing.
- When updating the root-served SPA, manually copy only `dist/index.html`, `dist/assets/`, and `dist/favicon.ico`.
- Do not overwrite root `about.html`, `contact.html`, or `terms.html` during manual copy.

## Routing Model

- Vue should own the SPA entry and client-side app routes.
- Root `about.html`, `contact.html`, and `terms.html` should be served as real static files.
- Frontend API calls should stay same-origin and relative, for example `/api/test`.
- CodeIgniter owns `/api/*`.
- Root `.htaccess` should serve real files directly and only fall back to `/index.html` for non-file SPA routes.

## Conventions

- Do not hand-edit compiled SPA files in `dist/`.
- Use `frontend-src/` only for Vue SPA source.
- Author `about.html`, `contact.html`, and `terms.html` directly in the repository root.
- Treat root SEO pages as the source of truth for static content.
- Manually copy only SPA build files from `dist/` into root.
- Do not overwrite root SEO pages during manual copy.
- Do not mix frontend source files into `api/` or `codeigniter/`.
- Keep backend endpoints under `/api/*`.

## Notes

- Bootstrap utility classes can be used later for UI styling.
