<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class ServiceOrderModel extends Model
{
    protected $table            = 'app_service_orders';
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
        'order_status',
        'note',
        'received_at',
        'ready_at',
        'delivered_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps    = false;

    /**
     * Default order list ordered newest first for service history screens.
     */
    public function findByBook(
        string $bookId,
        ?string $receivedFrom = null,
        ?string $receivedTo = null,
        ?string $orderStatus = null,
        ?string $paymentStatus = null,
        string $search = '',
        int $page = 1,
        int $perPage = 20
    ): array {
        $currentPage = max(1, $page);
        $itemsPerPage = max(1, $perPage);
        $totalItems = $this->countByBook($bookId, $receivedFrom, $receivedTo, $orderStatus, $paymentStatus, $search);
        $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $itemsPerPage;

        $orders = $this->makeOrderListQuery($bookId, $receivedFrom, $receivedTo, $orderStatus, $paymentStatus, $search)
            ->orderBy('app_service_orders.received_at', 'DESC')
            ->orderBy('app_service_orders.created_at', 'DESC')
            ->limit($itemsPerPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'orders' => $orders,
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
    public function findExistingByIdAndBook(string $bookId, string $orderId): ?array
    {
        if ($bookId === '' || $orderId === '') {
            return null;
        }

        $order = $this->makeOrderSummaryQuery()
            ->where('app_service_orders.id', $orderId)
            ->where('app_service_orders.book_id', $bookId)
            ->where('app_service_orders.deleted_at', null)
            ->first();

        return $order ?: null;
    }

    /**
     * Returns orders linked to one customer inside the same book.
     */
    public function findByBookAndCustomer(string $bookId, string $customerId): array
    {
        if ($bookId === '' || $customerId === '') {
            return [];
        }

        return $this->makeOrderSummaryQuery()
            ->where('app_service_orders.book_id', $bookId)
            ->where('app_service_orders.customer_id', $customerId)
            ->where('app_service_orders.deleted_at', null)
            ->orderBy('app_service_orders.received_at', 'DESC')
            ->orderBy('app_service_orders.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Compact totals for dashboard-style summary cards.
     */
    public function findAnalyticsSummaryByBook(
        string $bookId,
        ?string $receivedFrom = null,
        ?string $receivedTo = null
    ): array {
        $query = $this->builder()
            ->select([
                'COUNT(app_service_orders.id) AS order_count',
                'COALESCE(SUM(app_service_orders.discount_amount), 0) AS total_discount_amount',
                'COALESCE(SUM(app_service_orders.total_amount), 0) AS total_amount',
                'COALESCE(SUM(app_service_orders.paid_amount), 0) AS paid_amount',
                'COALESCE(SUM(app_service_orders.due_amount), 0) AS due_amount',
            ])
            ->where('app_service_orders.book_id', $bookId)
            ->where('app_service_orders.deleted_at', null);

        if ($receivedFrom !== null) {
            $query->where('app_service_orders.received_at >=', $receivedFrom);
        }

        if ($receivedTo !== null) {
            $query->where('app_service_orders.received_at <=', $receivedTo);
        }

        $row = $query->get()->getRowArray() ?? [];

        return [
            'order_count' => (int) ($row['order_count'] ?? 0),
            'total_discount_amount' => $this->formatAggregateMoney($row['total_discount_amount'] ?? 0),
            'total_amount' => $this->formatAggregateMoney($row['total_amount'] ?? 0),
            'paid_amount' => $this->formatAggregateMoney($row['paid_amount'] ?? 0),
            'due_amount' => $this->formatAggregateMoney($row['due_amount'] ?? 0),
        ];
    }

    private function makeOrderSummaryQuery(?array $columns = null): self
    {
        return $this->select($columns ?? $this->orderSummaryColumns())
            ->select($this->orderItemCountSelect(), false)
            ->join(
            'app_service_customers',
            'app_service_customers.id = app_service_orders.customer_id'
            . ' AND app_service_customers.deleted_at IS NULL',
            'left'
        );
    }

    private function makeOrderListQuery(
        string $bookId,
        ?string $receivedFrom = null,
        ?string $receivedTo = null,
        ?string $orderStatus = null,
        ?string $paymentStatus = null,
        string $search = ''
    ): BaseBuilder {
        $query = $this->builder()
            ->select($this->orderSummaryColumns())
            ->select($this->orderItemCountSelect(), false)
            ->join(
                'app_service_customers',
                'app_service_customers.id = app_service_orders.customer_id'
                . ' AND app_service_customers.deleted_at IS NULL',
                'left'
            )
            ->where('app_service_orders.book_id', $bookId)
            ->where('app_service_orders.deleted_at', null);

        if ($receivedFrom !== null) {
            $query->where('app_service_orders.received_at >=', $receivedFrom);
        }

        if ($receivedTo !== null) {
            $query->where('app_service_orders.received_at <=', $receivedTo);
        }

        if ($orderStatus !== null && $orderStatus !== '') {
            $query->where('app_service_orders.order_status', $orderStatus);
        }

        if ($paymentStatus !== null && $paymentStatus !== '') {
            $query->where('app_service_orders.payment_status', $paymentStatus);
        }

        $normalizedSearch = trim($search);

        if ($normalizedSearch !== '') {
            $query->join(
                'app_service_order_items',
                'app_service_order_items.order_id = app_service_orders.id',
                'left'
            )->groupStart()
                ->like('app_service_orders.id', $normalizedSearch)
                ->orLike('app_service_customers.name', $normalizedSearch)
                ->orLike('app_service_customers.phone', $normalizedSearch)
                ->orLike('app_service_order_items.object_name', $normalizedSearch)
                ->orLike('app_service_order_items.service_name', $normalizedSearch)
                ->groupEnd()
                ->distinct();
        }

        return $query;
    }

    private function countByBook(
        string $bookId,
        ?string $receivedFrom = null,
        ?string $receivedTo = null,
        ?string $orderStatus = null,
        ?string $paymentStatus = null,
        string $search = ''
    ): int {
        $query = $this->builder()
            ->select('COUNT(DISTINCT app_service_orders.id) AS total_items', false)
            ->join(
                'app_service_customers',
                'app_service_customers.id = app_service_orders.customer_id'
                . ' AND app_service_customers.deleted_at IS NULL',
                'left'
            )
            ->where('app_service_orders.book_id', $bookId)
            ->where('app_service_orders.deleted_at', null);

        if ($receivedFrom !== null) {
            $query->where('app_service_orders.received_at >=', $receivedFrom);
        }

        if ($receivedTo !== null) {
            $query->where('app_service_orders.received_at <=', $receivedTo);
        }

        if ($orderStatus !== null && $orderStatus !== '') {
            $query->where('app_service_orders.order_status', $orderStatus);
        }

        if ($paymentStatus !== null && $paymentStatus !== '') {
            $query->where('app_service_orders.payment_status', $paymentStatus);
        }

        $normalizedSearch = trim($search);

        if ($normalizedSearch !== '') {
            $query->join(
                'app_service_order_items',
                'app_service_order_items.order_id = app_service_orders.id',
                'left'
            )->groupStart()
                ->like('app_service_orders.id', $normalizedSearch)
                ->orLike('app_service_customers.name', $normalizedSearch)
                ->orLike('app_service_customers.phone', $normalizedSearch)
                ->orLike('app_service_order_items.object_name', $normalizedSearch)
                ->orLike('app_service_order_items.service_name', $normalizedSearch)
                ->groupEnd();
        }

        return (int) ($query->get()->getRowArray()['total_items'] ?? 0);
    }

    private function orderSummaryColumns(): array
    {
        return [
            'app_service_orders.id',
            'app_service_orders.book_id',
            'app_service_orders.created_by',
            'app_service_orders.customer_id',
            'app_service_orders.currency_code',
            'app_service_orders.subtotal_amount',
            'app_service_orders.discount_amount',
            'app_service_orders.total_amount',
            'app_service_orders.paid_amount',
            'app_service_orders.due_amount',
            'app_service_orders.payment_status',
            'app_service_orders.order_status',
            'app_service_orders.note',
            'app_service_orders.received_at',
            'app_service_orders.ready_at',
            'app_service_orders.delivered_at',
            'app_service_orders.created_at',
            'app_service_orders.updated_at',
            'app_service_customers.name AS customer_name',
            'app_service_customers.phone AS customer_phone',
            'app_service_customers.messenger AS customer_messenger',
            'app_service_customers.address AS customer_address',
            'app_service_customers.location AS customer_location',
        ];
    }

    private function orderItemCountSelect(): string
    {
        return '(SELECT COUNT(*) FROM app_service_order_items'
            . ' WHERE app_service_order_items.order_id = app_service_orders.id) AS item_count';
    }

    private function formatAggregateMoney(mixed $value): string
    {
        $amount = is_numeric($value) ? (float) $value : 0.0;

        return number_format($amount, 2, '.', '');
    }
}
