<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
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
    public function findByBook(
        string $bookId,
        ?string $soldFrom = null,
        ?string $soldTo = null,
        string $search = '',
        int $page = 1,
        int $perPage = 20
    ): array
    {
        $currentPage = max(1, $page);
        $itemsPerPage = max(1, $perPage);
        $totalItems = $this->countByBook($bookId, $soldFrom, $soldTo, $search);
        $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $itemsPerPage;

        $sales = $this->makeSaleListQuery($bookId, $soldFrom, $soldTo, $search)
            ->orderBy('minishop_sales.sold_at', 'DESC')
            ->orderBy('minishop_sales.created_at', 'DESC')
            ->limit($itemsPerPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'sales' => $sales,
            'pagination' => [
                'page' => $currentPage,
                'per_page' => $itemsPerPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ],
        ];
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

    public function findAnalyticsSummaryByBook(
        string $bookId,
        ?string $soldFrom = null,
        ?string $soldTo = null
    ): array {
        $query = $this->builder()
            ->select([
                'COUNT(minishop_sales.id) AS sale_count',
                'COALESCE(SUM(minishop_sales.discount_amount), 0) AS total_discount_amount',
                'COALESCE(SUM(minishop_sales.total_amount), 0) AS total_amount',
                'COALESCE(SUM(minishop_sales.paid_amount), 0) AS paid_amount',
                'COALESCE(SUM(minishop_sales.due_amount), 0) AS due_amount',
            ])
            ->where('minishop_sales.book_id', $bookId)
            ->where('minishop_sales.deleted_at', null);

        if ($soldFrom !== null) {
            $query->where('minishop_sales.sold_at >=', $soldFrom);
        }

        if ($soldTo !== null) {
            $query->where('minishop_sales.sold_at <=', $soldTo);
        }

        $row = $query->get()->getRowArray() ?? [];

        return [
            'sale_count' => (int) ($row['sale_count'] ?? 0),
            'total_discount_amount' => $this->formatAggregateMoney($row['total_discount_amount'] ?? 0),
            'total_amount' => $this->formatAggregateMoney($row['total_amount'] ?? 0),
            'paid_amount' => $this->formatAggregateMoney($row['paid_amount'] ?? 0),
            'due_amount' => $this->formatAggregateMoney($row['due_amount'] ?? 0),
        ];
    }

    private function makeSaleSummaryQuery(?array $columns = null): self
    {
        return $this->select($columns ?? $this->saleSummaryColumns())->join(
            'minishop_customers',
            'minishop_customers.id = minishop_sales.customer_id'
            . ' AND minishop_customers.deleted_at IS NULL',
            'left'
        );
    }

    private function makeSaleListQuery(
        string $bookId,
        ?string $soldFrom = null,
        ?string $soldTo = null,
        string $search = ''
    ): BaseBuilder {
        $query = $this->builder()
            ->select($this->saleSummaryColumns())
            ->join(
                'minishop_customers',
                'minishop_customers.id = minishop_sales.customer_id'
                . ' AND minishop_customers.deleted_at IS NULL',
                'left'
            )
            ->where('minishop_sales.book_id', $bookId)
            ->where('minishop_sales.deleted_at', null);

        if ($soldFrom !== null) {
            $query->where('minishop_sales.sold_at >=', $soldFrom);
        }

        if ($soldTo !== null) {
            $query->where('minishop_sales.sold_at <=', $soldTo);
        }

        $normalizedSearch = trim($search);

        if ($normalizedSearch !== '') {
            $query->join(
                'minishop_sale_items',
                'minishop_sale_items.sale_id = minishop_sales.id',
                'left'
            )->groupStart()
                ->like('minishop_sales.id', $normalizedSearch)
                ->orLike('minishop_customers.name', $normalizedSearch)
                ->orLike('minishop_sale_items.product_name', $normalizedSearch)
                ->orLike('minishop_sale_items.product_sku', $normalizedSearch)
                ->groupEnd()
                ->distinct();
        }

        return $query;
    }

    private function countByBook(
        string $bookId,
        ?string $soldFrom = null,
        ?string $soldTo = null,
        string $search = ''
    ): int {
        $query = $this->builder()
            ->select('COUNT(DISTINCT minishop_sales.id) AS total_items', false)
            ->join(
                'minishop_customers',
                'minishop_customers.id = minishop_sales.customer_id'
                . ' AND minishop_customers.deleted_at IS NULL',
                'left'
            )
            ->where('minishop_sales.book_id', $bookId)
            ->where('minishop_sales.deleted_at', null);

        if ($soldFrom !== null) {
            $query->where('minishop_sales.sold_at >=', $soldFrom);
        }

        if ($soldTo !== null) {
            $query->where('minishop_sales.sold_at <=', $soldTo);
        }

        $normalizedSearch = trim($search);

        if ($normalizedSearch !== '') {
            $query->join(
                'minishop_sale_items',
                'minishop_sale_items.sale_id = minishop_sales.id',
                'left'
            )->groupStart()
                ->like('minishop_sales.id', $normalizedSearch)
                ->orLike('minishop_customers.name', $normalizedSearch)
                ->orLike('minishop_sale_items.product_name', $normalizedSearch)
                ->orLike('minishop_sale_items.product_sku', $normalizedSearch)
                ->groupEnd();
        }

        return (int) ($query->get()->getRowArray()['total_items'] ?? 0);
    }

    private function saleSummaryColumns(): array
    {
        return [
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
        ];
    }

    private function formatAggregateMoney(mixed $value): string
    {
        $amount = is_numeric($value) ? (float) $value : 0.0;

        return number_format($amount, 2, '.', '');
    }
}
