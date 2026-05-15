<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopProductModel extends Model
{
    protected $table            = 'minishop_products';
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

    public function findByBook(string $bookId, bool $activeOnly = false): array
    {
        $query = $this->select([
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
        ])->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->select('(low_stock_alert IS NOT NULL AND quantity <= low_stock_alert) AS is_low_stock', false);

        if ($activeOnly) {
            $query->where('is_active', 1);
        }

        return $query->orderBy('name', 'ASC')
            ->findAll();
    }

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
}
