<?php

namespace App\Services;

use App\Models\BookModel;

/**
 * Business-level access rules for books.
 *
 * Controllers should call this service instead of reaching straight into
 * BookModel when they need to answer "can this user access this book?".
 * The model still owns the raw query shape.
 */
class BookAccessService
{
    public function __construct(
        private readonly BookModel $books = new BookModel()
    ) {
    }

    /**
     * Returns the book only when it belongs to the user, is active, and
     * optionally matches the expected type for the calling controller.
     *
     * Used by NotesController, TodosController, and FinanceController before
     * loading type-specific records.
     */
    public function getAccessibleBook(string $userId, string $bookId, ?string $typeKey = null): ?array
    {
        if ($userId === '' || $bookId === '') {
            return null;
        }

        // Delegate the persistence details to BookModel. The service keeps the
        // controller-facing meaning: "give me a book this user may access".
        return $this->books->findOwnedActiveBook($userId, $bookId, $typeKey);
    }
}
