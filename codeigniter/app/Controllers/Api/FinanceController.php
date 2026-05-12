<?php

namespace App\Controllers\Api;

use App\Models\FinanceTransactionModel;
use App\Services\BookAccessService;

/**
 * Authenticated finance-book content endpoint.
 *
 * Route: GET /api/books/{bookId}/finance
 *
 * Even though the route is `/finance`, the payload key remains `transactions`
 * because the finance mini app currently exposes transaction rows.
 */
class FinanceController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly FinanceTransactionModel $transactions = new FinanceTransactionModel()
    ) {
    }

    public function index(string $bookId)
    {
        $userId = $this->currentUserIdForRead();

        // Finance books use the same access rule path as other book types.
        $book   = $this->bookAccess->getAccessibleBook($userId, $bookId, 'finance');

        if ($book === null) {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            // FinanceTransactionModel provides the actual finance row query.
            'transactions' => $this->transactions->findByBook($bookId),
        ]);
    }
}
