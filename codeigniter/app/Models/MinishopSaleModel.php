<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopSaleModel extends Model
{
    protected $table            = 'minishop_sales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'id',
        'book_id',
        'created_by',
        'customer_id',
        'currency_code',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'note',
        'sold_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps    = false;

    public function findByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'book_id',
            'created_by',
            'customer_id',
            'currency_code',
            'subtotal_amount',
            'discount_amount',
            'total_amount',
            'paid_amount',
            'due_amount',
            'payment_status',
            'note',
            'sold_at',
            'created_at',
            'updated_at',
        ])->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('sold_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function findExistingByIdAndBook(string $bookId, string $saleId): ?array
    {
        if ($bookId === '' || $saleId === '') {
            return null;
        }

        $sale = $this->where('id', $saleId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $sale ?: null;
    }

    public function findOneForReporting(string $bookId, string $saleId): ?array
    {
        if ($bookId === '' || $saleId === '') {
            return null;
        }

        $sale = $this->select([
            'id',
            'book_id',
            'customer_id',
            'currency_code',
            'subtotal_amount',
            'discount_amount',
            'total_amount',
            'paid_amount',
            'due_amount',
            'payment_status',
            'sold_at',
        ])->where('id', $saleId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $sale ?: null;
    }
}
