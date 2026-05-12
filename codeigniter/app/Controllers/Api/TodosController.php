<?php

namespace App\Controllers\Api;

use App\Models\TodoModel;
use App\Services\BookAccessService;

/**
 * Authenticated todo-book content endpoint.
 *
 * Route: GET /api/books/{bookId}/todos
 */
class TodosController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly TodoModel $todos = new TodoModel()
    ) {
    }

    public function index(string $bookId)
    {
        $userId = $this->currentUserIdForRead();

        // Reuse the shared book access rule so the controller does not repeat
        // ownership, archive, delete, and type checks inline.
        $book   = $this->bookAccess->getAccessibleBook($userId, $bookId, 'todo');

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            // TodoModel should only be called after the parent book is validated.
            'todos' => $this->todos->findByBook($bookId),
        ]);
    }
}
