# frontend-src

This directory contains the frontend source code for `oqkitob-com`.

## Role of This Folder

- Edit frontend code only in this folder.
- Keep Vue SPA pages, components, router, styles, and static frontend assets here.
- Do not maintain standalone SEO pages in this folder.
- Production-like SPA artifacts are generated into the root-level `dist/` folder.

## Development Commands

Install dependencies:

```sh
npm install
```

Start the Vite development server:

```sh
npm run dev
```

Build the frontend into `../dist`:

```sh
npm run build
```

Preview the built frontend locally:

```sh
npm run preview
```

## Notes

- API requests should use relative paths such as `/api/test`.
- The backend remains separate under `../api` and `../codeigniter`.
- The Vue SPA source lives in `src/`.
- Standalone SEO HTML pages are maintained in the repository root, not inside `frontend-src/`.
- When testing the root-served SPA, manually copy `dist/index.html`, `dist/assets/`, and `dist/favicon.ico` to the repository root.
