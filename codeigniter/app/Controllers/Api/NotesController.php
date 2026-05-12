<?php

namespace App\Controllers\Api;

use App\Models\NoteModel;
use App\Services\BookAccessService;

/**
 * Authenticated note-book content endpoint.
 *
 * Route: GET /api/books/{bookId}/notes
 */
class NotesController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly NoteModel $notes = new NoteModel()
    ) {
    }

    public function index(string $bookId)
    {
        // The shared authenticated base gives us the session user ID and
        // releases the session lock because this endpoint is read-only.
        $userId = $this->currentUserIdForRead();

        // Always resolve the parent book first. This keeps ownership and type
        // validation in one place before querying the notes table.
        $book   = $this->bookAccess->getAccessibleBook($userId, $bookId, 'notes');

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            // NoteModel owns the note query details once access is confirmed.
            'notes' => $this->notes->findByBook($bookId),
        ]);
    }
}
