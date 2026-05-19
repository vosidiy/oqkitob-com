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

    /**
     * Default sales list ordered newest first for history screens.
     */
    public function findByBook(string $bookId, ?string $soldFrom = null, ?string $soldTo = null): array
    {
        $query = $this->makeSaleSummaryQuery()
            ->where('minishop_sales.book_id', $bookId)
            ->where('minishop_sales.deleted_at', null);

        if ($soldFrom !== null) {
            $query->where('minishop_sales.sold_at >=', $soldFrom);
        }

        if ($soldTo !== null) {
            $query->where('minishop_sales.sold_at <=', $soldTo);
        }

        return $query->orderBy('minishop_sales.sold_at', 'DESC')
            ->orderBy('minishop_sales.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Book-scoped lookup for detail pages and write operations.
     */
    public function findExistingByIdAndBook(string $bookId, string $saleId): ?array
    {
        if ($bookId === '' || $saleId === '') {
            return null;
        }

        $sale = $this->makeSaleSummaryQuery()
            ->where('minishop_sales.id', $saleId)
            ->where('minishop_sales.book_id', $bookId)
            ->where('minishop_sales.deleted_at', null)
            ->first();

        return $sale ?: null;
    }

    /**
     * Lightweight projection for reporting flows that do not need notes or
     * creator metadata.
     */
    public function findOneForReporting(string $bookId, string $saleId): ?array
    {
        if ($bookId === '' || $saleId === '') {
            return null;
        }

        $sale = $this->makeSaleSummaryQuery([
            'minishop_sales.id',
            'minishop_sales.book_id',
            'minishop_sales.customer_id',
            'minishop_sales.currency_code',
            'minishop_sales.subtotal_amount',
            'minishop_sales.discount_amount',
            'minishop_sales.total_amount',
            'minishop_sales.paid_amount',
            'minishop_sales.due_amount',
            'minishop_sales.payment_status',
            'minishop_sales.sold_at',
            'minishop_customers.name AS customer_name',
            'minishop_customers.phone AS customer_phone',
        ])->where('minishop_sales.id', $saleId)
            ->where('minishop_sales.book_id', $bookId)
            ->where('minishop_sales.deleted_at', null)
            ->first();

        return $sale ?: null;
    }

    public function findByBookAndCustomer(string $bookId, string $customerId): array
    {
        if ($bookId === '' || $customerId === '') {
            return [];
        }

        return $this->makeSaleSummaryQuery()
            ->where('minishop_sales.book_id', $bookId)
            ->where('minishop_sales.customer_id', $customerId)
            ->where('minishop_sales.deleted_at', null)
            ->orderBy('minishop_sales.sold_at', 'DESC')
            ->orderBy('minishop_sales.created_at', 'DESC')
            ->findAll();
    }

    private function makeSaleSummaryQuery(?array $columns = null): self
    {
        return $this->select($columns ?? [
            'minishop_sales.id',
            'minishop_sales.book_id',
            'minishop_sales.created_by',
            'minishop_sales.customer_id',
            'minishop_sales.currency_code',
            'minishop_sales.subtotal_amount',
            'minishop_sales.discount_amount',
            'minishop_sales.total_amount',
            'minishop_sales.paid_amount',
            'minishop_sales.due_amount',
            'minishop_sales.payment_status',
            'minishop_sales.note',
            'minishop_sales.sold_at',
            'minishop_sales.created_at',
            'minishop_sales.updated_at',
            'minishop_customers.name AS customer_name',
            'minishop_customers.phone AS customer_phone',
        ])->join(
            'minishop_customers',
            'minishop_customers.id = minishop_sales.customer_id'
            . ' AND minishop_customers.deleted_at IS NULL',
            'left'
        );
    }
}
