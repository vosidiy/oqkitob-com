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
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'todo');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            // TodoModel should only be called after the parent book is validated.
            'todos' => $this->todos->findByBook($bookId),
        ]);
    }
}
