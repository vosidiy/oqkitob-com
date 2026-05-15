<?php

namespace App\Controllers\Api;

use App\Models\MinishopCategoryModel;
use App\Models\MinishopProductModel;
use App\Services\BookAccessService;

/**
 * Authenticated minishop product endpoints.
 *
 * Routes:
 * GET  /api/books/{bookId}/minishop/products
 * POST /api/books/{bookId}/minishop/products
 */
class MinishopProductsController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly MinishopProductModel $products = new MinishopProductModel(),
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
            'products' => $this->products->findByBook($bookId, true),
        ]);
    }

    public function create(string $bookId)
    {
        helper('uuid');

        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'minishop');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $payload = $this->getProductPayload();
        $error = $this->validateProductPayload($bookId, $payload);

        if ($error !== null) {
            return $this->respond(['message' => $error], 422);
        }

        $timestamp = $this->utcNow();
        $productId = uuid_v4();

        $created = $this->products->insert([
            'id' => $productId,
            'book_id' => $bookId,
            'created_by' => $userId,
            'category_id' => $payload['category_id'],
            'name' => $payload['name'],
            'sku' => $payload['sku'],
            'price' => $payload['price'],
            'quantity' => $payload['quantity'],
            'low_stock_alert' => $payload['low_stock_alert'],
            'is_active' => 1,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($created === false) {
            return $this->failServerError('Unable to create product right now.');
        }

        $product = $this->products->findListRowByIdAndBook($bookId, $productId);

        if ($product === null) {
            return $this->failServerError('Unable to load the new product right now.');
        }

        return $this->respond([
            'message' => 'Product created successfully.',
            'product' => $product,
        ], 201);
    }

    private function getProductPayload(): array
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getRawInput();
        }

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $categoryId = trim((string) ($payload['category_id'] ?? ''));
        $sku = trim((string) ($payload['sku'] ?? ''));
        $price = trim((string) ($payload['price'] ?? ''));
        $quantity = trim((string) ($payload['quantity'] ?? ''));
        $lowStockAlert = trim((string) ($payload['low_stock_alert'] ?? ''));

        return [
            'name' => $name,
            'category_id' => $categoryId !== '' ? $categoryId : null,
            'sku' => $sku !== '' ? $sku : null,
            'price' => $price !== '' ? $price : null,
            'quantity' => $quantity !== '' ? $quantity : null,
            'low_stock_alert' => $lowStockAlert !== '' ? $lowStockAlert : null,
        ];
    }

    private function validateProductPayload(string $bookId, array $payload): ?string
    {
        $name = (string) ($payload['name'] ?? '');

        if ($name === '') {
            return 'Product name is required.';
        }

        if (mb_strlen($name) > 255) {
            return 'Product name must be 255 characters or fewer.';
        }

        $categoryId = $payload['category_id'] ?? null;

        if ($categoryId !== null && $this->categories->findExistingByIdAndBook($bookId, (string) $categoryId) === null) {
            return 'Please choose a valid category.';
        }

        $sku = $payload['sku'] ?? null;

        if ($sku !== null && mb_strlen((string) $sku) > 100) {
            return 'SKU must be 100 characters or fewer.';
        }

        $price = $payload['price'] ?? null;

        if ($price === null || ! is_numeric($price)) {
            return 'Please enter a valid product price.';
        }

        if ((float) $price < 0) {
            return 'Product price cannot be negative.';
        }

        $quantity = $payload['quantity'] ?? null;

        if ($quantity === null || ! is_numeric($quantity)) {
            return 'Please enter a valid product quantity.';
        }

        if ((float) $quantity < 0) {
            return 'Product quantity cannot be negative.';
        }

        $lowStockAlert = $payload['low_stock_alert'] ?? null;

        if ($lowStockAlert !== null && ! is_numeric($lowStockAlert)) {
            return 'Please enter a valid low-stock alert quantity.';
        }

        if ($lowStockAlert !== null && (float) $lowStockAlert < 0) {
            return 'Low-stock alert cannot be negative.';
        }

        return null;
    }
}
