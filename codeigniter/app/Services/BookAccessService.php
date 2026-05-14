<?php

namespace App\Services;

use App\Models\BookModel;

/**
 * Business-level access rules for books.
 *
 * Controllers should call this service when they need book permission checks.
 */
class BookAccessService
{
    public function __construct(
        private readonly BookModel $books = new BookModel()
    ) {
    }

    /**
     * Returns the user's permission level for the book.
     *
     * The MVP only distinguishes between owner and no access, but this return
     * value is intentionally future-friendly for shared books.
     */
    public function getUserBookPermission(string $userId, string $bookId, ?string $typeKey = null): string
    {
        if ($userId === '' || $bookId === '') {
            return 'none';
        }

        return $this->books->hasOwnedActiveBook($userId, $bookId, $typeKey)
            ? 'owner'
            : 'none';
    }
}
