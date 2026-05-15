<?php

namespace App\Controllers\Api;

use App\Models\MinishopCategoryModel;
use App\Services\BookAccessService;

/**
 * Authenticated minishop category endpoints.
 *
 * Route: GET /api/books/{bookId}/minishop/categories
 */
class MinishopCategoriesController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly MinishopCategoryModel $categories = new MinishopCategoryModel()
    ) {
    }

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
}
