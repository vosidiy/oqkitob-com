<?php

namespace App\Models;

use CodeIgniter\Model;

class FinanceTransactionModel extends Model
{
    protected $table          = 'finance_transactions';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = false;

    public function findByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'book_id',
            'category_id',
            'type',
            'amount',
            'currency_code',
            'transaction_date',
            'note',
            'reference',
            'created_at',
            'updated_at',
        ])->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
