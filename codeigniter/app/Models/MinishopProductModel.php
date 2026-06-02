<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopProductModel extends Model
{
    private const LIST_COLUMNS = [
        'id',
        'book_id',
        'created_by',
        'category_id',
        'name',
        'sku',
        'price',
        'quantity',
        'low_stock_alert',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $table            = 'app_minishop_products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'id',
        'book_id',
        'created_by',
        'category_id',
        'name',
        'sku',
        'price',
        'quantity',
        'low_stock_alert',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps    = false;

    /**
     * Adds a computed low-stock flag so list endpoints do not need to
     * duplicate the alert rule in PHP.
     */
    public function findByBook(string $bookId, bool $activeOnly = false): array
    {
        $query = $this->select(self::LIST_COLUMNS)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->select('(low_stock_alert IS NOT NULL AND quantity <= low_stock_alert) AS is_low_stock', false);

        if ($activeOnly) {
            $query->where('is_active', 1);
        }

        return $query->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Book-scoped lookup for sale creation and stock updates.
     */
    public function findExistingByIdAndBook(string $bookId, string $productId): ?array
    {
        if ($bookId === '' || $productId === '') {
            return null;
        }

        $product = $this->where('id', $productId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $product ?: null;
    }

    /**
     * Returns one list-shaped product row after create requests.
     */
    public function findListRowByIdAndBook(string $bookId, string $productId): ?array
    {
        if ($bookId === '' || $productId === '') {
            return null;
        }

        $product = $this->select(self::LIST_COLUMNS)
            ->select('(low_stock_alert IS NOT NULL AND quantity <= low_stock_alert) AS is_low_stock', false)
            ->where('id', $productId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $product ?: null;
    }

    /**
     * Clears category links for active products in the given book.
     *
     * @param list<string> $categoryIds
     */
    public function clearCategoryAssignmentsByBook(string $bookId, array $categoryIds, string $timestamp): void
    {
        $normalizedIds = array_values(array_filter(array_map(static fn ($categoryId) => trim((string) $categoryId), $categoryIds)));

        if ($bookId === '' || $normalizedIds === []) {
            return;
        }

        $this->builder()
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->whereIn('category_id', $normalizedIds)
            ->update([
                'category_id' => null,
                'updated_at' => $timestamp,
            ]);
    }
}
