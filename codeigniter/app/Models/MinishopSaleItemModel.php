<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopSaleItemModel extends Model
{
    protected $table            = 'minishop_sale_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id',
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'line_total',
    ];
    protected $useTimestamps    = false;

    /**
     * Sale items are stored as snapshots, so ordering by ID preserves the
     * original insert sequence well enough for MVP detail views.
     */
    public function findBySale(string $saleId): array
    {
        return $this->select([
            'id',
            'sale_id',
            'product_id',
            'product_name',
            'product_sku',
            'quantity',
            'unit_price',
            'line_total',
        ])->where('sale_id', $saleId)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function findProductAnalyticsByBook(
        string $bookId,
        ?string $soldFrom = null,
        ?string $soldTo = null
    ): array {
        $query = $this->builder()
            ->select([
                'minishop_sale_items.product_id',
                'minishop_sale_items.product_name',
                'minishop_sale_items.product_sku',
                'COALESCE(SUM(minishop_sale_items.quantity), 0) AS units_sold',
                'COALESCE(SUM(minishop_sale_items.line_total), 0) AS total_amount',
            ])
            ->join('minishop_sales', 'minishop_sales.id = minishop_sale_items.sale_id')
            ->where('minishop_sales.book_id', $bookId)
            ->where('minishop_sales.deleted_at', null);

        if ($soldFrom !== null) {
            $query->where('minishop_sales.sold_at >=', $soldFrom);
        }

        if ($soldTo !== null) {
            $query->where('minishop_sales.sold_at <=', $soldTo);
        }

        $rows = $query->groupBy('minishop_sale_items.product_id')
            ->groupBy('minishop_sale_items.product_name')
            ->groupBy('minishop_sale_items.product_sku')
            ->orderBy('total_amount', 'DESC')
            ->orderBy('units_sold', 'DESC')
            ->orderBy('minishop_sale_items.product_name', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(function (array $row): array {
            return [
                'product_id' => $row['product_id'] ?? null,
                'product_name' => (string) ($row['product_name'] ?? ''),
                'product_sku' => $row['product_sku'] ?? null,
                'units_sold' => $this->formatAggregateQuantity($row['units_sold'] ?? 0),
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
