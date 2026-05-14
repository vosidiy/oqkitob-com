<?php

namespace App\Controllers\Api;

use App\Models\BookModel;
use App\Models\BookTypeModel;

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
        private readonly BookModel $books = new BookModel(),
        private readonly BookTypeModel $bookTypes = new BookTypeModel()
    ) {
    }

    public function index()
    {
        // Read-only endpoint: fetch the user ID first, then release the
        // session lock before running the book query.
        $userId = $this->currentUserIdAndCloseSession();

        return $this->respond([
            'books' => $this->books->findSidebarBooksForUser($userId),
        ]);
    }

    public function show(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $book   = $this->books->findOwnedActiveBook($userId, $bookId);

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            'book' => $book,
        ]);
    }

    public function types()
    {
        // This endpoint is read-only, so release the session lock after
        // confirming the request is authenticated.
        $this->currentUserIdAndCloseSession();

        return $this->respond([
            'bookTypes' => $this->bookTypes->findActiveForSelection(),
        ]);
    }

    public function create()
    {
        helper('uuid');

        // New books belong to the authenticated user, so create still needs the
        // session-backed user ID even in this MVP flow.
        $userId  = $this->currentUserId();
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $title   = trim((string) ($payload['title'] ?? ''));
        $typeKey = trim((string) ($payload['type_key'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));

        if ($title === '') {
            return $this->respond([
                'message' => 'Book name is required.',
            ], 422);
        }

        if (mb_strlen($title) > 255) {
            return $this->respond([
                'message' => 'Book name must be 255 characters or fewer.',
            ], 422);
        }

        if ($typeKey === '') {
            return $this->respond([
                'message' => 'Book type is required.',
            ], 422);
        }

        if (! $this->bookTypes->isActiveTypeKey($typeKey)) {
            return $this->respond([
                'message' => 'Please select a valid book type.',
            ], 422);
        }

        $bookId = uuid_v4();

        $created = $this->books->insert([
            'id' => $bookId,
            'user_id' => $userId,
            'type_key' => $typeKey,
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'is_archived' => 0,
            'sort_order' => $this->books->findNextSortOrderForUser($userId),
        ]);

        if ($created === false) {
            return $this->failServerError('Unable to create book right now.');
        }

        $book = $this->books->findSidebarBookByIdForUser($userId, $bookId);

        if ($book === null) {
            return $this->failServerError('Unable to load the new book right now.');
        }

        return $this->respond([
            'message' => 'Book created successfully.',
            'book' => $book,
        ], 201);
    }
}
