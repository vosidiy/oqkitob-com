<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    private const ACTIVE_SIDEBAR_COLUMNS = [
        'id',
        'title',
        'type_key',
        'currency_code',
        'description',
        'icon',
        'color',
        'show_cents',
        'thousand_separator',
        'is_archived',
    ];
    private const ARCHIVED_SIDEBAR_COLUMNS = [
        'id',
        'title',
        'type_key',
        'currency_code',
        'description',
        'icon',
        'color',
        'show_cents',
        'thousand_separator',
        'is_archived',
    ];

    protected $table          = 'books';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = false;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'id',
        'user_id',
        'type_key',
        'currency_code',
        'title',
        'description',
        'icon',
        'color',
        'show_cents',
        'thousand_separator',
        'is_archived',
        'sort_order',
        'last_opened_at',
        'deleted_at',
    ];
    protected $useTimestamps  = false;
    protected $beforeUpdate   = ['stripImmutableBookFields'];

    /**
     * Sidebar-only projection for the authenticated shell.
     *
     * BooksController::index() uses this instead of a full book query because
     * the sidebar only needs lightweight metadata.
     */
    public function findSidebarBooksForUser(string $userId): array
    {
        return $this->select(self::ACTIVE_SIDEBAR_COLUMNS)
            ->where('user_id', $userId)
            ->where('deleted_at', null)
            ->where('is_archived', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Archived sidebar projection for the authenticated shell.
     */
    public function findArchivedSidebarBooksForUser(string $userId): array
    {
        return $this->select(self::ARCHIVED_SIDEBAR_COLUMNS)
            ->where('user_id', $userId)
            ->where('deleted_at', null)
            ->where('is_archived', 1)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Returns one sidebar-formatted book after a successful create request.
     */
    public function findSidebarBookByIdForUser(string $userId, string $bookId): ?array
    {
        if ($userId === '' || $bookId === '') {
            return null;
        }

        $book = $this->select(self::ACTIVE_SIDEBAR_COLUMNS)
            ->where('id', $bookId)
            ->where('user_id', $userId)
            ->where('deleted_at', null)
            ->first();

        return $book ?: null;
    }

    /**
     * Returns one owned book regardless of archive status.
     */
    public function findOwnedBookById(string $userId, string $bookId): ?array
    {
        if ($userId === '' || $bookId === '') {
            return null;
        }

        $book = $this->select([
            'id',
            'user_id',
            'type_key',
            'currency_code',
            'title',
            'description',
            'show_cents',
            'thousand_separator',
            'is_archived',
        ])->where('id', $bookId)
            ->where('user_id', $userId)
            ->where('deleted_at', null)
            ->first();

        return $book ?: null;
    }

    /**
     * Returns the book row for an owned archived book when the caller needs it.
     */
    public function findOwnedArchivedBook(string $userId, string $bookId): ?array
    {
        if ($userId === '' || $bookId === '') {
            return null;
        }

        $book = $this->select([
            'id',
            'user_id',
            'type_key',
            'currency_code',
            'title',
            'description',
            'show_cents',
            'thousand_separator',
            'is_archived',
        ])->where('id', $bookId)
            ->where('user_id', $userId)
            ->where('deleted_at', null)
            ->where('is_archived', 1)
            ->first();

        return $book ?: null;
    }

    /**
     * Returns the book row for an owned active book when the caller needs it.
     */
    public function findOwnedActiveBook(string $userId, string $bookId, ?string $typeKey = null): ?array
    {
        if ($userId === '' || $bookId === '') {
            return null;
        }

        $query = $this->select([
            'id',
            'user_id',
            'type_key',
            'currency_code',
            'title',
            'description',
            'show_cents',
            'thousand_separator',
            'is_archived',
        ])->where('id', $bookId)
            ->where('user_id', $userId)
            ->where('deleted_at', null)
            ->where('is_archived', 0);

        // Type-specific controllers pass the expected type key here so a
        // `/notes` endpoint cannot accidentally read a `/finance` book.
        if ($typeKey !== null) {
            $query->where('type_key', $typeKey);
        }

        $book = $query->first();

        return $book ?: null;
    }

    /**
     * Returns one active book row without applying any ownership rule.
     *
     * Callers should only use this after a higher-level access service has
     * already confirmed the current user may see the book.
     */
    public function findActiveBookById(string $bookId, ?string $typeKey = null): ?array
    {
        if ($bookId === '') {
            return null;
        }

        $query = $this->select([
            'id',
            'user_id',
            'type_key',
            'currency_code',
            'title',
            'description',
            'show_cents',
            'thousand_separator',
            'is_archived',
        ])->where('id', $bookId)
            ->where('deleted_at', null)
            ->where('is_archived', 0);

        if ($typeKey !== null) {
            $query->where('type_key', $typeKey);
        }

        $book = $query->first();

        return $book ?: null;
    }

    /**
     * Lightweight permission/existence check for an owned active book.
     */
    public function hasOwnedActiveBook(string $userId, string $bookId, ?string $typeKey = null): bool
    {
        if ($userId === '' || $bookId === '') {
            return false;
        }

        $query = $this->select('id')
            ->where('id', $bookId)
            ->where('user_id', $userId)
            ->where('deleted_at', null)
            ->where('is_archived', 0);

        if ($typeKey !== null) {
            $query->where('type_key', $typeKey);
        }

        return $query->first() !== null;
    }

    /**
     * Book type and currency are fixed once a book exists.
     *
     * The controller also validates this rule, but the model strips these
     * fields as a final safeguard so later callers cannot mutate them by
     * accident through a generic update().
     */
    protected function stripImmutableBookFields(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        unset($data['data']['type_key'], $data['data']['currency_code']);

        return $data;
    }
}
