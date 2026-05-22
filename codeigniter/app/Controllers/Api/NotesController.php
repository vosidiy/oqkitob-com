<?php

namespace App\Controllers\Api;

use App\Models\NoteModel;
use App\Services\BookAccessService;

/**
 * Authenticated note-book content endpoints.
 *
 * Routes:
 * GET    /api/books/{bookId}/notes
 * POST   /api/books/{bookId}/notes
 * PUT    /api/books/{bookId}/notes/{noteId}
 * POST   /api/books/{bookId}/notes/{noteId}/pin
 * POST   /api/books/{bookId}/notes/{noteId}/unpin
 * POST   /api/books/{bookId}/notes/{noteId}/archive
 * DELETE /api/books/{bookId}/notes/{noteId}
 */
class NotesController extends AuthenticatedApiController
{
    private const ALLOWED_COLORS = ['yellow', 'purple', 'white', 'blue', 'green', 'red'];

    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly NoteModel $notes = new NoteModel()
    ) {
    }

    public function index(string $bookId)
    {
        // Notes endpoints do not mutate session state, so release the lock as
        // soon as we have the authenticated user ID.
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'notes');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            // NoteModel owns the note query details once access is confirmed.
            'notes' => $this->notes->findByBook($bookId),
        ]);
    }

    public function create(string $bookId)
    {
        helper('uuid');

        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'notes');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $payload = $this->getNotePayload();
        $error   = $this->validateNotePayload($payload);

        if ($error !== null) {
            return $this->respond(['message' => $error], 422);
        }

        $timestamp = $this->utcNow();
        $noteId    = uuid_v4();

        $created = $this->notes->insert([
            'id' => $noteId,
            'book_id' => $bookId,
            'created_by' => $userId,
            'title' => $payload['title'],
            'content' => $payload['content'],
            'color' => $payload['color'],
            'position' => 0,
            'is_pinned' => 0,
            'is_archived' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($created === false) {
            return $this->failServerError('Unable to create note right now.');
        }

        return $this->respond([
            'message' => 'Note created successfully.',
            'note' => $this->makeNoteResponse([
                'id' => $noteId,
                'book_id' => $bookId,
                'created_by' => $userId,
                'title' => $payload['title'],
                'content' => $payload['content'],
                'color' => $payload['color'],
                'position' => 0,
                'is_pinned' => 0,
                'is_archived' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ]),
        ], 201);
    }

    public function update(string $bookId, string $noteId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'notes');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $note = $this->notes->findExistingByIdAndBook($bookId, $noteId);

        if ($note === null) {
            return $this->failNotFound('Note not found.');
        }

        $payload = $this->getNotePayload();
        $error   = $this->validateNotePayload($payload);

        if ($error !== null) {
            return $this->respond(['message' => $error], 422);
        }

        $timestamp = $this->utcNow();

        $updated = $this->notes->update($noteId, [
            'title' => $payload['title'],
            'content' => $payload['content'],
            'color' => $payload['color'],
            'updated_at' => $timestamp,
        ]);

        if ($updated === false) {
            return $this->failServerError('Unable to update note right now.');
        }

        return $this->respond([
            'message' => 'Note updated successfully.',
            'note' => $this->makeNoteResponse([
                ...$note,
                'title' => $payload['title'],
                'content' => $payload['content'],
                'color' => $payload['color'],
                'updated_at' => $timestamp,
            ]),
        ]);
    }

    public function archive(string $bookId, string $noteId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'notes');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $note = $this->notes->findExistingByIdAndBook($bookId, $noteId);

        if ($note === null) {
            return $this->failNotFound('Note not found.');
        }

        $timestamp = $this->utcNow();

        $updated = $this->notes->update($noteId, [
            'is_archived' => 1,
            'updated_at' => $timestamp,
        ]);

        if ($updated === false) {
            return $this->failServerError('Unable to archive note right now.');
        }

        return $this->respond([
            'message' => 'Note archived successfully.',
            'noteId' => $noteId,
            'is_archived' => 1,
            'updated_at' => $timestamp,
        ]);
    }

    public function pin(string $bookId, string $noteId)
    {
        return $this->setPinnedState($bookId, $noteId, 1, 'Note pinned successfully.');
    }

    public function unpin(string $bookId, string $noteId)
    {
        return $this->setPinnedState($bookId, $noteId, 0, 'Note unpinned successfully.');
    }

    public function delete(string $bookId, string $noteId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'notes');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $note = $this->notes->findExistingByIdAndBook($bookId, $noteId);

        if ($note === null) {
            return $this->failNotFound('Note not found.');
        }

        $timestamp = $this->utcNow();

        $deleted = $this->notes->update($noteId, [
            'deleted_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($deleted === false) {
            return $this->failServerError('Unable to delete note right now.');
        }

        return $this->respond([
            'message' => 'Note deleted successfully.',
            'noteId' => $noteId,
            'deleted_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    private function getNotePayload(): array
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getRawInput();
        }

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        $title = trim((string) ($payload['title'] ?? ''));
        $content = trim((string) ($payload['content'] ?? ''));
        $color = trim((string) ($payload['color'] ?? ''));

        return [
            'title' => $title !== '' ? $title : null,
            'content' => $content !== '' ? $content : null,
            'color' => $color !== '' ? $color : null,
        ];
    }

    private function validateNotePayload(array $payload): ?string
    {
        if (($payload['title'] ?? null) === null && ($payload['content'] ?? null) === null) {
            return 'Please enter a title or note content.';
        }

        if (($payload['title'] ?? null) !== null && mb_strlen((string) $payload['title']) > 255) {
            return 'Note title must be 255 characters or fewer.';
        }

        $color = $payload['color'] ?? null;

        if ($color !== null && ! in_array($color, self::ALLOWED_COLORS, true)) {
            return 'Please choose a valid note color.';
        }

        return null;
    }

    private function setPinnedState(string $bookId, string $noteId, int $isPinned, string $message)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'notes');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $note = $this->notes->findExistingByIdAndBook($bookId, $noteId);

        if ($note === null) {
            return $this->failNotFound('Note not found.');
        }

        $timestamp = $this->utcNow();

        $updated = $this->notes->update($noteId, [
            'is_pinned' => $isPinned,
            'updated_at' => $timestamp,
        ]);

        if ($updated === false) {
            return $this->failServerError('Unable to update note pin right now.');
        }

        return $this->respond([
            'message' => $message,
            'noteId' => $noteId,
            'is_pinned' => $isPinned,
            'updated_at' => $timestamp,
        ]);
    }

    private function makeNoteResponse(array $note): array
    {
        return [
            'id' => $note['id'],
            'book_id' => $note['book_id'],
            'created_by' => $note['created_by'] ?? null,
            'title' => $note['title'] ?? null,
            'content' => $note['content'] ?? null,
            'color' => $note['color'] ?? null,
            'position' => (int) ($note['position'] ?? 0),
            'is_pinned' => (int) ($note['is_pinned'] ?? 0),
            'is_archived' => (int) ($note['is_archived'] ?? 0),
            'created_at' => $note['created_at'] ?? null,
            'updated_at' => $note['updated_at'] ?? null,
            'deleted_at' => $note['deleted_at'] ?? null,
        ];
    }
}
