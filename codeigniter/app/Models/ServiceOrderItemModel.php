<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceOrderItemModel extends Model
{
    protected $table            = 'app_service_order_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id',
        'order_id',
        'service_type_id',
        'object_name',
        'service_name',
        'quantity',
        'unit_code',
        'unit_price',
        'line_total',
        'note',
        'attachment_path',
        'sort_order',
    ];
    protected $useTimestamps    = false;

    /**
     * Order items are stored as snapshots, and explicit sort_order preserves
     * the line sequence from order entry screens.
     */
    public function findByOrder(string $orderId): array
    {
        return $this->select([
            'id',
            'order_id',
            'service_type_id',
            'object_name',
            'service_name',
            'quantity',
            'unit_code',
            'unit_price',
            'line_total',
            'note',
            'attachment_path',
            'sort_order',
        ])->where('order_id', $orderId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Suggests recently used object names from past orders in the same book.
     */
    public function findRecentObjectNameSuggestionsByBook(
        string $bookId,
        ?string $serviceTypeId = null,
        int $limit = 10
    ): array {
        if ($bookId === '') {
            return [];
        }

        $query = $this->builder()
            ->select([
                'app_service_order_items.object_name',
                'COUNT(app_service_order_items.id) AS usage_count',
                'MAX(COALESCE(app_service_orders.received_at, app_service_orders.created_at)) AS last_used_at',
            ])
            ->join('app_service_orders', 'app_service_orders.id = app_service_order_items.order_id')
            ->where('app_service_orders.book_id', $bookId)
            ->where('app_service_orders.deleted_at', null)
            ->where('app_service_order_items.object_name <>', '');

        if ($serviceTypeId !== null && $serviceTypeId !== '') {
            $query->where('app_service_order_items.service_type_id', $serviceTypeId);
        }

        return $query->groupBy('app_service_order_items.object_name')
            ->orderBy('last_used_at', 'DESC')
            ->orderBy('usage_count', 'DESC')
            ->orderBy('app_service_order_items.object_name', 'ASC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();
    }

    public function findServiceAnalyticsByBook(
        string $bookId,
        ?string $receivedFrom = null,
        ?string $receivedTo = null,
        ?string $orderStatus = null
    ): array {
        if ($bookId === '') {
            return [];
        }

        $query = $this->builder()
            ->select([
                'app_service_order_items.service_name',
                'app_service_order_items.unit_code',
                'COALESCE(SUM(app_service_order_items.quantity), 0) AS total_quantity',
                'COALESCE(SUM(app_service_order_items.line_total), 0) AS total_amount',
            ])
            ->join('app_service_orders', 'app_service_orders.id = app_service_order_items.order_id')
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

        $rows = $query->groupBy('app_service_order_items.service_name')
            ->groupBy('app_service_order_items.unit_code')
            ->orderBy('total_amount', 'DESC')
            ->orderBy('app_service_order_items.service_name', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(function (array $row): array {
            return [
                'service_name' => (string) ($row['service_name'] ?? ''),
                'unit_code' => (string) ($row['unit_code'] ?? 'qty'),
                'total_quantity' => $this->formatAggregateQuantity($row['total_quantity'] ?? 0),
                'total_amount' => $this->formatAggregateMoney($row['total_amount'] ?? 0),
            ];
        }, $rows);
    }

    private function formatAggregateMoney(mixed $value): string
    {
        $amount = is_numeric($value) ? (float) $value : 0.0;

        return number_format($amount, 2, '.', '');
    }

    private function formatAggregateQuantity(mixed $value): string
    {
        $quantity = is_numeric($value) ? (float) $value : 0.0;

        return number_format($quantity, 3, '.', '');
    }
}
