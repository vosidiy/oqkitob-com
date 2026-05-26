# Database Design

## Database Goals

The schema should support:

- user accounts
- secure web authentication
- many books per user
- one book type per book
- separate tables for separate book types
- clean expansion when new book types are introduced later

## General Conventions

- MySQL with `utf8mb4`
- `InnoDB` engine
- Primary entity IDs stored as `CHAR(36)` UUID strings
- Timestamps stored in UTC
- Table names in plural snake_case
- Foreign keys used where practical

## Core Tables

### `users`

Stores account profile data and account-level settings.

Key notes:

- `email` must be unique
- `password_hash` stores the hashed password
- `default_book_id` points to the user's preferred book
- `email_verified_at` indicates verification status

### `ci_sessions`

Stores CodeIgniter session data for web authentication.

This supports:

- persistent server-side sessions
- same-origin SPA login state
- shared-hosting-friendly session storage

### `user_email_verifications`

Stores short-lived verification codes separately from `users`.

Why separate table:

- avoids repeatedly mutating permanent user records for temporary codes
- cleaner audit trail
- easier expiry handling

### `password_reset_tokens`

Stores password reset requests separately from `users`.

Recommended rule:

- store token hash, not the raw token itself

## Book Tables

### `book_types`

Lookup table for supported book types.

Initial values:

- `notes`
- `todo`
- `finance`
- `minishop`

Future values can be added later:

- `sales`
- others

Current capability flag:

- `requires_currency` tells the create flow whether books of that type must store a book-level currency

### `books`

Central table that connects a user to a book type.

Each book row includes:

- owner user
- `type_key`
- optional `currency_code`
- title
- optional description
- optional JSON settings
- archive state

Rule:

- `currency_code` is immutable after book creation and is only required for book types where `book_types.requires_currency = 1`
- the code format is intentionally relaxed for now; application validation only limits it to a short value

Core rule:

- feature tables never exist on their own; they always belong to a `book_id`

## Feature Tables Included In The Initial SQL

### `notes`

For books of type `notes`.

Each row is a note entry linked to one `book_id`.

Suggested behavior:

- optional title
- full text body
- pinned flag
- ordering support
- soft delete support

### `todos`

For books of type `todo`.

Each row is a task linked to one `book_id`.

Suggested behavior:

- title
- optional description
- completion state
- due date
- priority
- ordering support

### `finance_categories`

For books of type `finance`.

Each category belongs to one finance book.

Suggested behavior:

- name
- `income` or `expense` type
- optional color

### `finance_transactions`

For books of type `finance`.

Each transaction belongs to one finance book and can reference a category.

Suggested behavior:

- amount
- currency code
- transaction date
- transaction type
- note/reference fields

## Data Ownership Rules

These rules should be enforced in application logic:

1. A user may only access books they own. 
2. A user may only access notes/todos/finance rows through their own books.
3. `users.default_book_id` must belong to the same user.
4. A `notes` row must only be used for a book where `books.type_key = 'notes'`.
5. A `todos` row must only be used for a book where `books.type_key = 'todo'`.
6. Finance rows must only be used for a book where `books.type_key = 'finance'`.

## Why Separate Tables Per Book Type

This is the right fit for the product because:

- each book type has different fields
- validation rules differ
- queries stay simpler
- features can evolve independently
- mobile and web clients can reuse clear APIs

Using one giant polymorphic data table would make validation, indexing, and maintenance harder.

## Future Tables Not Included Yet

These are intentionally not in the first SQL file:

- `sales_people`
- `sales_products`
- `sales_payments`
- `book_members`
- `notifications`
- `audit_logs`
- mobile token/auth tables

They can be added once the MVP flow is stable.

## Suggested Expansion Pattern For New Book Types

When adding a new type later:

1. Add the type to `book_types`
2. Create dedicated tables for the new type
3. Create backend routes/controllers/services for that type
4. Create frontend screens/components for that type
5. Enforce that the feature endpoints only work for matching book types

## Schema Summary

```text
users
  -> books
      -> notes
      -> todos
      -> finance_categories
      -> finance_transactions
      -> minishop_categories
      -> minishop_products
      -> minishop_customers
      -> minishop_sales
      -> minishop_sale_items
      -> minishop_sale_payments

users
  -> user_email_verifications
  -> password_reset_tokens

web auth
  -> ci_sessions
```
