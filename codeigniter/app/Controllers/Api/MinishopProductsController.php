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
 * Authenticated minishop product endpoints.
 *
 * Routes:
 * GET  /api/books/{bookId}/minishop/products
 * POST /api/books/{bookId}/minishop/products
 * PUT  /api/books/{bookId}/minishop/products/{productId}
 * POST /api/books/{bookId}/minishop/products/{productId}/deactivate
 */
class MinishopProductsController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly MinishopProductModel $products = new MinishopProductModel(),
        private readonly MinishopCategoryModel $categories = new MinishopCategoryModel(),
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
            'products' => $this->products->findByBook($bookId, true),
        ]);
    }

    public function create(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'minishop');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $payload = $this->getProductPayload();

        try {
            $product = $this->createProductWithResolvedCategory($userId, $bookId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to create product right now.');
        }

        return $this->respond([
            'message' => 'Product created successfully.',
            'product' => $product,
        ], 201);
    }

    public function update(string $bookId, string $productId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'minishop');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $product = $this->products->findExistingByIdAndBook($bookId, $productId);

        if ($product === null) {
            return $this->failNotFound('Product not found.');
        }

        $payload = $this->getProductPayload();

        try {
            $product = $this->updateProductWithResolvedCategory($userId, $bookId, $productId, $product, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to update product right now.');
        }

        return $this->respond([
            'message' => 'Product updated successfully.',
            'product' => $product,
        ]);
    }

    public function deactivate(string $bookId, string $productId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'minishop');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $product = $this->products->findExistingByIdAndBook($bookId, $productId);

        if ($product === null) {
            return $this->failNotFound('Product not found.');
        }

        $timestamp = $this->utcNow();

        $updated = $this->products->update($productId, [
            'is_active' => 0,
            'updated_at' => $timestamp,
        ]);

        if ($updated === false) {
            return $this->failServerError('Unable to deactivate product right now.');
        }

        return $this->respond([
            'message' => 'Product deactivated successfully.',
            'product_id' => $productId,
            'updated_at' => $timestamp,
        ]);
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
        $newCategoryName = trim((string) ($payload['new_category_name'] ?? ''));
        $sku = trim((string) ($payload['sku'] ?? ''));
        $price = trim((string) ($payload['price'] ?? ''));
        $quantity = trim((string) ($payload['quantity'] ?? ''));
        $lowStockAlert = trim((string) ($payload['low_stock_alert'] ?? ''));

        return [
            'name' => $name,
            'category_id' => $categoryId !== '' ? $categoryId : null,
            'new_category_name' => $newCategoryName !== '' ? $newCategoryName : null,
            'sku' => $sku !== '' ? $sku : null,
            'price' => $price !== '' ? $price : null,
            'quantity' => $quantity !== '' ? $quantity : null,
            'low_stock_alert' => $lowStockAlert !== '' ? $lowStockAlert : null,
        ];
    }

    private function validateProductPayload(string $bookId, array $payload): void
    {
        $name = (string) ($payload['name'] ?? '');

        if ($name === '') {
            throw new InvalidArgumentException('Product name is required.');
        }

        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Product name must be 255 characters or fewer.');
        }

        $newCategoryName = $payload['new_category_name'] ?? null;

        if ($newCategoryName !== null && mb_strlen((string) $newCategoryName) > 255) {
            throw new InvalidArgumentException('Category name must be 255 characters or fewer.');
        }

        $categoryId = $payload['category_id'] ?? null;

        if (
            $newCategoryName === null
            && $categoryId !== null
            && $this->categories->findExistingByIdAndBook($bookId, (string) $categoryId) === null
        ) {
            throw new InvalidArgumentException('Please choose a valid category.');
        }

        $sku = $payload['sku'] ?? null;

        if ($sku !== null && mb_strlen((string) $sku) > 100) {
            throw new InvalidArgumentException('SKU must be 100 characters or fewer.');
        }

        $price = $payload['price'] ?? null;

        if ($price === null || ! is_numeric($price)) {
            throw new InvalidArgumentException('Please enter a valid product price.');
        }

        if ((float) $price < 0) {
            throw new InvalidArgumentException('Product price cannot be negative.');
        }

        $quantity = $payload['quantity'] ?? null;

        if ($quantity === null || ! is_numeric($quantity)) {
            throw new InvalidArgumentException('Please enter a valid product quantity.');
        }

        if ((float) $quantity < 0) {
            throw new InvalidArgumentException('Product quantity cannot be negative.');
        }

        $lowStockAlert = $payload['low_stock_alert'] ?? null;

        if ($lowStockAlert !== null && ! is_numeric($lowStockAlert)) {
            throw new InvalidArgumentException('Please enter a valid low-stock alert quantity.');
        }

        if ($lowStockAlert !== null && (float) $lowStockAlert < 0) {
            throw new InvalidArgumentException('Low-stock alert cannot be negative.');
        }
    }

    private function createProductWithResolvedCategory(string $userId, string $bookId, array $payload): array
    {
        helper('uuid');

        $this->validateProductPayload($bookId, $payload);

        $timestamp = $this->utcNow();
        $productId = uuid_v4();

        $this->db->transException(true)->transStart();

        try {
            $categoryId = $this->resolveProductCategoryId($userId, $bookId, $payload, $timestamp);

            $created = $this->products->insert([
                'id' => $productId,
                'book_id' => $bookId,
                'created_by' => $userId,
                'category_id' => $categoryId,
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
                throw new RuntimeException('Unable to create product right now.');
            }

            $this->db->transComplete();
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        $product = $this->products->findListRowByIdAndBook($bookId, $productId);

        if ($product === null) {
            throw new RuntimeException('Unable to load the new product right now.');
        }

        return $product;
    }

    private function updateProductWithResolvedCategory(
        string $userId,
        string $bookId,
        string $productId,
        array $existingProduct,
        array $payload
    ): array {
        $this->validateProductPayload($bookId, $payload);

        $timestamp = $this->utcNow();

        $this->db->transException(true)->transStart();

        try {
            $categoryId = $this->resolveProductCategoryId($userId, $bookId, $payload, $timestamp);

            $updated = $this->products->update($productId, [
                'category_id' => $categoryId,
                'name' => $payload['name'],
                'sku' => $payload['sku'],
                'price' => $payload['price'],
                'quantity' => $payload['quantity'],
                'low_stock_alert' => $payload['low_stock_alert'],
                'updated_at' => $timestamp,
            ]);

            if ($updated === false) {
                throw new RuntimeException('Unable to update product right now.');
            }

            $this->db->transComplete();
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        return $this->makeProductResponse($bookId, $productId, [
            ...$existingProduct,
            'category_id' => $categoryId ?? null,
            'name' => $payload['name'],
            'sku' => $payload['sku'],
            'price' => $payload['price'],
            'quantity' => $payload['quantity'],
            'low_stock_alert' => $payload['low_stock_alert'],
            'updated_at' => $timestamp,
        ]);
    }

    private function resolveProductCategoryId(string $userId, string $bookId, array $payload, string $timestamp): ?string
    {
        $newCategoryName = $payload['new_category_name'] ?? null;

        if ($newCategoryName !== null) {
            $existingCategory = $this->categories->findExistingByNameAndBook($bookId, (string) $newCategoryName);

            if ($existingCategory !== null) {
                return (string) $existingCategory['id'];
            }

            helper('uuid');
            $categoryId = uuid_v4();

            $created = $this->categories->insert([
                'id' => $categoryId,
                'book_id' => $bookId,
                'created_by' => $userId,
                'name' => $newCategoryName,
                'sort_order' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            if ($created === false) {
                throw new RuntimeException('Unable to create category right now.');
            }

            return $categoryId;
        }

        return $payload['category_id'] ?? null;
    }

    private function makeProductResponse(string $bookId, string $productId, array $fallback): array
    {
        $product = $this->products->findListRowByIdAndBook($bookId, $productId);

        if ($product !== null) {
            return $product;
        }

        $quantity = (float) ($fallback['quantity'] ?? 0);
        $lowStockAlert = $fallback['low_stock_alert'] ?? null;
        $isLowStock = $lowStockAlert !== null && (float) $lowStockAlert >= 0 && $quantity <= (float) $lowStockAlert;

        return [
            'id' => $productId,
            'book_id' => $bookId,
            'created_by' => $fallback['created_by'] ?? null,
            'category_id' => $fallback['category_id'] ?? null,
            'name' => $fallback['name'] ?? '',
            'sku' => $fallback['sku'] ?? null,
            'price' => $fallback['price'] ?? null,
            'quantity' => $fallback['quantity'] ?? null,
            'low_stock_alert' => $fallback['low_stock_alert'] ?? null,
            'is_active' => $fallback['is_active'] ?? 1,
            'created_at' => $fallback['created_at'] ?? null,
            'updated_at' => $fallback['updated_at'] ?? null,
            'is_low_stock' => $isLowStock ? 1 : 0,
        ];
    }
}
