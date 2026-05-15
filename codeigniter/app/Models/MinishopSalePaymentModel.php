<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopSalePaymentModel extends Model
{
    protected $table            = 'minishop_sale_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id',
        'sale_id',
        'created_by',
        'currency_code',
        'amount',
        'paid_at',
        'note',
        'created_at',
    ];
    protected $useTimestamps    = false;

    /**
     * Newest-first ordering matches the usual "recent payments" view.
     */
    public function findBySale(string $saleId): array
    {
        return $this->select([
            'id',
            'sale_id',
            'created_by',
            'currency_code',
            'amount',
            'paid_at',
            'note',
            'created_at',
        ])->where('sale_id', $saleId)
            ->orderBy('paid_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Sale-scoped lookup for payment edits or deletes.
     */
    public function findExistingByIdAndSale(string $saleId, string $paymentId): ?array
    {
        if ($saleId === '' || $paymentId === '') {
            return null;
        }

        $payment = $this->where('id', $paymentId)
            ->where('sale_id', $saleId)
            ->first();

        return $payment ?: null;
    }

    /**
     * Summary helpers use this to refresh the parent sale's cached paid total.
     */
    public function sumAmountBySale(string $saleId): float
    {
        if ($saleId === '') {
            return 0.0;
        }

        $row = $this->selectSum('amount', 'total_paid')
            ->where('sale_id', $saleId)
            ->first();

        return (float) ($row['total_paid'] ?? 0);
    }
}
