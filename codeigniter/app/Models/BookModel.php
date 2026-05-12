<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table          = 'books';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'user_id',
        'type_key',
        'title',
        'description',
        'icon',
        'color',
        'settings_json',
        'is_archived',
        'sort_order',
        'last_opened_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps  = false;

    /**
     * Sidebar-only projection for the authenticated shell.
     *
     * BooksController::index() uses this instead of a full book query because
     * the sidebar only needs lightweight metadata.
     */
    public function findSidebarBooksForUser(string $userId): array
    {
        return $this->select([
            'id',
            'title',
            'type_key',
            'description',
            'icon',
            'color',
            'is_archived',
        ])->where('user_id', $userId)
            ->where('deleted_at', null)
            ->where('is_archived', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Low-level book access query.
     *
     * BookAccessService wraps this so controllers can talk in terms of access
     * rules while the model stays responsible for the SQL conditions.
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
            'title',
            'description',
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
}
