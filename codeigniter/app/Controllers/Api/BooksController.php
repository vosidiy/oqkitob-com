<?php

namespace App\Controllers\Api;

use App\Models\BookModel;
use App\Services\BookAccessService;

/**
 * Authenticated book metadata endpoints.
 *
 * This controller intentionally stays narrow: it serves the shared sidebar
 * book list for the frontend shell. Type-specific content lives in
 * NotesController, TodosController, and FinanceController.
 */
class BooksController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly BookModel $books = new BookModel()
    ) {
    }

    public function index()
    {
        // Read-only endpoint: fetch the user ID first, then release the
        // session lock before running the book query.
        $userId = $this->currentUserIdForRead();

        return $this->respond([
            'books' => $this->books->findSidebarBooksForUser($userId),
        ]);
    }

    public function show(string $bookId)
    {
        $userId = $this->currentUserIdForRead();
        $book   = $this->bookAccess->getAccessibleBook($userId, $bookId);

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            'book' => $book,
        ]);
    }
}
