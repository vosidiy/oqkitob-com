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
}
