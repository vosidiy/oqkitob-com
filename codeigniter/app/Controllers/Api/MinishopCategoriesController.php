<?php

namespace App\Controllers\Api;

use App\Models\MinishopCategoryModel;
use App\Models\MinishopProductModel;
use App\Services\BookAccessService;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authenticated minishop category endpoints.
 *
 * Route: GET /api/books/{bookId}/minishop/categories
 */
class MinishopCategoriesController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly MinishopCategoryModel $categories = new MinishopCategoryModel(),
        private readonly MinishopProductModel $products = new MinishopProductModel(),
        ?BaseConnection $db = null
    ) {
        $this->db = $db ?? Database::connect();
    }

    private BaseConnection $db;

    public function index(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'minishop');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            'categories' => $this->categories->findSelectionByBook($bookId),
        ]);
    }

    public function manage(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'minishop');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        try {
            $payload = $this->getManagePayload();
            $this->saveManagedCategories($userId, $bookId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to save categories right now.');
        }

        return $this->respond([
            'message' => 'Categories saved successfully.',
            'categories' => $this->categories->findSelectionByBook($bookId),
        ]);
    }

    private function getManagePayload(): array
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getRawInput();
        }

        if (! is_array($payload)) {
            $payload = [];
        }

        $categories = $payload['categories'] ?? null;

        if (! is_array($categories)) {
            throw new InvalidArgumentException('Please provide categories to save.');
        }

        return array_map(static function ($row): array {
            $category = is_array($row) ? $row : [];

            return [
                'id' => trim((string) ($category['id'] ?? '')),
                'name' => trim((string) ($category['name'] ?? '')),
                'remove' => (bool) ($category['remove'] ?? false),
            ];
        }, $categories);
    }

    private function saveManagedCategories(string $userId, string $bookId, array $rows): void
    {
        $existingIds = [];
        $seenNames = [];

        foreach ($rows as $row) {
            $id = $row['id'];
            $name = $row['name'];
            $remove = $row['remove'];
            $isNewCategory = $id === '';

            if ($id !== '') {
                $existingIds[] = $id;
            }

            if ($remove) {
                continue;
            }

            if ($isNewCategory && $name === '') {
                continue;
            }

            if ($name === '') {
                throw new InvalidArgumentException('Existing category names cannot be empty.');
            }

            if (mb_strlen($name) > 255) {
                throw new InvalidArgumentException('Category names must be 255 characters or fewer.');
            }

            $normalizedNameKey = mb_strtolower($name);

            if (isset($seenNames[$normalizedNameKey])) {
                throw new InvalidArgumentException('Category names must be unique.');
            }

            $seenNames[$normalizedNameKey] = true;
        }

        $existingIds = array_values(array_unique($existingIds));
        $existingCategories = $this->categories->findExistingByIdsAndBook($bookId, $existingIds);
        $existingById = [];

        foreach ($existingCategories as $category) {
            $existingById[(string) $category['id']] = $category;
        }

        foreach ($existingIds as $existingId) {
            if (! isset($existingById[$existingId])) {
                throw new InvalidArgumentException('Please refresh and try again. One or more categories no longer exist.');
            }
        }

        helper('uuid');
        $timestamp = $this->utcNow();

        $this->db->transException(true)->transStart();

        try {
            $sortOrder = 0;
            $deleteIds = [];

            foreach ($rows as $row) {
                $id = $row['id'];
                $name = $row['name'];
                $remove = $row['remove'];

                if ($remove) {
                    if ($id !== '') {
                        $deleteIds[] = $id;
                    }

                    continue;
                }

                if ($id === '' && $name === '') {
                    continue;
                }

                if ($id === '') {
                    $created = $this->categories->insert([
                        'id' => uuid_v4(),
                        'book_id' => $bookId,
                        'created_by' => $userId,
                        'name' => $name,
                        'sort_order' => $sortOrder,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);

                    if ($created === false) {
                        throw new RuntimeException('Unable to create category right now.');
                    }
                } else {
                    $updated = $this->categories->update($id, [
                        'name' => $name,
                        'sort_order' => $sortOrder,
                        'updated_at' => $timestamp,
                    ]);

                    if ($updated === false) {
                        throw new RuntimeException('Unable to update category right now.');
                    }
                }

                $sortOrder++;
            }

            if ($deleteIds !== []) {
                $this->products->clearCategoryAssignmentsByBook($bookId, $deleteIds, $timestamp);

                foreach ($deleteIds as $deleteId) {
                    $deleted = $this->categories->delete($deleteId);

                    if ($deleted === false) {
                        throw new RuntimeException('Unable to delete category right now.');
                    }
                }
            }

            $this->db->transComplete();
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }
}
