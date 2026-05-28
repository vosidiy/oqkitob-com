<?php

namespace App\Controllers\Api;

use App\Models\BookModel;
use App\Models\BookTypeModel;
use App\Services\BookAccessService;
use App\Services\BookSettingsService;

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
        private readonly BookTypeModel $bookTypes = new BookTypeModel(),
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly BookSettingsService $bookSettings = new BookSettingsService()
    ) {
    }

    public function index()
    {
        // Read-only endpoint: fetch the user ID first, then release the
        // session lock before running the book query.
        $userId = $this->currentUserIdAndCloseSession();
        $books = $this->books->findSidebarBooksForUser($userId);

        return $this->respond([
            'books' => $this->formatBookList($books),
        ]);
    }

    public function archived()
    {
        $userId = $this->currentUserIdAndCloseSession();
        $books = $this->books->findArchivedSidebarBooksForUser($userId);

        return $this->respond([
            'books' => $books,
        ]);
    }

    public function show(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId);

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        // Access is checked through the service first, so this lookup only
        // needs to ensure the book is still active and readable.
        $book = $this->books->findActiveBookById($bookId);

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            'book' => $this->formatBookResponse($book),
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
        $userId = $this->currentUserIdAndCloseSession();
        $payload = $this->getBookPayload();

        // Normalize once up front so validation and insert logic both use the
        // same cleaned values regardless of JSON, form-data, or raw input.
        $title = trim((string) ($payload['title'] ?? ''));
        $typeKey = trim((string) ($payload['type_key'] ?? ''));
        $description = $this->normalizeOptionalDescription($payload['description'] ?? null);
        $currencyCode = $this->normalizeCurrencyCode($payload['currency_code'] ?? null);

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

        $bookType = $this->bookTypes->findActiveByTypeKey($typeKey);

        if ($bookType === null) {
            return $this->respond([
                'message' => 'Please select a valid book type.',
            ], 422);
        }

        // Currency requirements come from the selected active book type, not
        // from a hardcoded controller list, so the API and UI stay aligned.
        if ($this->bookTypeRequiresCurrency($bookType)) {
            if ($currencyCode === '') {
                return $this->respond([
                    'message' => 'Please choose a currency for this book.',
                ], 422);
            }

            if (mb_strlen($currencyCode) > 5) {
                return $this->respond([
                    'message' => 'Currency code must be 5 characters or fewer.',
                ], 422);
            }
        } else {
            // Ignore stray currency input for non-money books instead of
            // storing data the current book type does not use.
            $currencyCode = '';
        }

        $bookId = uuid_v4();
        $defaultSettings = $this->bookSettings->buildDefaultSettingsForBookType($bookType);

        $created = $this->books->insert([
            'id' => $bookId,
            'user_id' => $userId,
            'type_key' => $typeKey,
            'currency_code' => $currencyCode !== '' ? $currencyCode : null,
            'title' => $title,
            'description' => $description,
            'settings_json' => $this->bookSettings->encodeSettingsForStorage($defaultSettings),
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
            'book' => $this->formatBookResponse($book, $bookType),
        ], 201);
    }

    public function update(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId);

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $book = $this->books->findOwnedActiveBook($userId, $bookId);

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        $bookType = $this->resolveBookType((string) ($book['type_key'] ?? ''));
        $settingsSchema = $this->bookSettings->getSchemaForBookType($bookType);
        $payload = $this->getBookPayload();

        if (array_key_exists('type_key', $payload) || array_key_exists('currency_code', $payload)) {
            return $this->respond([
                'message' => 'Book type and currency cannot be changed after creation.',
            ], 422);
        }

        $title = trim((string) ($payload['title'] ?? ''));

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

        try {
            $encodedSettings = array_key_exists('settings', $payload)
                ? $this->bookSettings->encodeSettingsForStorage(
                    $this->bookSettings->normalizeSubmittedSettings($settingsSchema, $payload['settings'])
                )
                : ($book['settings_json'] ?? null);
        } catch (\InvalidArgumentException $exception) {
            return $this->respond([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $updated = $this->books->update($bookId, [
            'title' => $title,
            'description' => $this->normalizeOptionalDescription($payload['description'] ?? null),
            'settings_json' => $encodedSettings,
        ]);

        if ($updated === false) {
            return $this->failServerError('Unable to update book right now.');
        }

        $book = $this->books->findSidebarBookByIdForUser($userId, $bookId);

        if ($book === null) {
            return $this->failServerError('Unable to load the updated book right now.');
        }

        return $this->respond([
            'message' => 'Book updated successfully.',
            'book' => $this->formatBookResponse($book, $bookType),
        ]);
    }

    public function archive(string $bookId)
    {
        $userId = $this->currentUserId();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId);

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $archived = $this->books->update($bookId, [
            'is_archived' => 1,
        ]);

        if ($archived === false) {
            return $this->failServerError('Unable to archive book right now.');
        }

        return $this->respond([
            'message' => 'Book archived successfully.',
            'bookId' => $bookId,
            'is_archived' => 1,
        ]);
    }

    public function restore(string $bookId)
    {
        $userId = $this->currentUserId();
        $book = $this->books->findOwnedArchivedBook($userId, $bookId);

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        $restored = $this->books->update($bookId, [
            'is_archived' => 0,
        ]);

        if ($restored === false) {
            return $this->failServerError('Unable to restore book right now.');
        }

        return $this->respond([
            'message' => 'Book restored successfully.',
            'bookId' => $bookId,
            'is_archived' => 0,
        ]);
    }

    public function delete(string $bookId)
    {
        $userId = $this->currentUserId();
        $book = $this->books->findOwnedBookById($userId, $bookId);

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        $deletedAt = date('Y-m-d H:i:s');
        $deleted = $this->books->delete($bookId);

        if ($deleted === false) {
            return $this->failServerError('Unable to delete book right now.');
        }

        return $this->respond([
            'message' => 'Book deleted successfully.',
            'bookId' => $bookId,
            'deleted_at' => $deletedAt,
        ]);
    }

    private function getBookPayload(): array
    {
        // Accept JSON first, then fall back to raw input / form-data so the
        // endpoint works with the current SPA and simple manual API calls.
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getRawInput();
        }

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        return is_array($payload) ? $payload : [];
    }

    private function normalizeOptionalDescription(mixed $value): ?string
    {
        // Empty description is stored as NULL to keep the row canonical.
        $description = trim((string) ($value ?? ''));

        return $description !== '' ? $description : null;
    }

    private function normalizeCurrencyCode(mixed $value): string
    {
        // Currency is case-insensitive at input time, so store a normalized
        // uppercase code before validation and persistence.
        return strtoupper(trim((string) ($value ?? '')));
    }

    private function bookTypeRequiresCurrency(array $bookType): bool
    {
        // `book_types.requires_currency` is the authoritative capability flag.
        return (int) ($bookType['requires_currency'] ?? 0) === 1;
    }

    /**
     * Adds normalized settings fields to one book payload.
     */
    private function formatBookResponse(array $book, ?array $bookType = null): array
    {
        $bookType = $bookType ?? $this->resolveBookType((string) ($book['type_key'] ?? ''));
        $settingsSchema = $this->bookSettings->getSchemaForBookType($bookType);
        $settings = $this->bookSettings->normalizeStoredSettings($settingsSchema, $book['settings_json'] ?? null);

        unset($book['settings_json']);

        $book['settings'] = $settings;
        $book['settings_schema'] = $settingsSchema;

        return $book;
    }

    /**
     * Resolves one book type row for settings capability checks.
     */
    private function resolveBookType(string $typeKey): array
    {
        return $this->bookTypes->findByTypeKey($typeKey) ?? [
            'type_key' => $typeKey,
            'requires_currency' => 0,
        ];
    }

    /**
     * Adds normalized settings fields to active book list payloads.
     */
    private function formatBookList(array $books): array
    {
        if ($books === []) {
            return [];
        }

        $typeLookup = [];
        $typeRows = $this->bookTypes->findByTypeKeys(array_values(array_unique(array_map(
            static fn (array $book): string => trim((string) ($book['type_key'] ?? '')),
            $books
        ))));

        foreach ($typeRows as $typeRow) {
            $typeLookup[(string) $typeRow['type_key']] = $typeRow;
        }

        return array_map(function (array $book) use ($typeLookup): array {
            $typeKey = trim((string) ($book['type_key'] ?? ''));

            return $this->formatBookResponse($book, $typeLookup[$typeKey] ?? null);
        }, $books);
    }

}
