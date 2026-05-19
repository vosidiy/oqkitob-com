<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopCustomerModel extends Model
{
    protected $table            = 'minishop_customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'id',
        'book_id',
        'created_by',
        'name',
        'phone',
        'note',
        'reminder_at',
        'reminder_note',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps    = false;

    /**
     * Basic customer list for the current minishop book.
     */
    public function findByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'book_id',
            'created_by',
            'name',
            'phone',
            'note',
            'reminder_at',
            'reminder_note',
            'created_at',
            'updated_at',
        ])->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Full customer summary list for customer tabs and sale dropdown metadata.
     */
    public function findCustomerListByBook(string $bookId, string $search = ''): array
    {
        $query = $this->select([
            'minishop_customers.id',
            'minishop_customers.book_id',
            'minishop_customers.created_by',
            'minishop_customers.name',
            'minishop_customers.phone',
            'minishop_customers.note',
            'minishop_customers.reminder_at',
            'minishop_customers.reminder_note',
            'minishop_customers.created_at',
            'minishop_customers.updated_at',
            'COUNT(minishop_sales.id) AS receipt_count',
            'COALESCE(SUM(minishop_sales.total_amount), 0) AS total_sales_amount',
            'COALESCE(SUM(minishop_sales.paid_amount), 0) AS total_paid_amount',
            'COALESCE(SUM(minishop_sales.due_amount), 0) AS outstanding_balance',
            'MAX(minishop_sales.sold_at) AS last_sale_at',
        ])->join(
            'minishop_sales',
            'minishop_sales.customer_id = minishop_customers.id'
            . ' AND minishop_sales.deleted_at IS NULL',
            'left'
        )->where('minishop_customers.book_id', $bookId)
            ->where('minishop_customers.deleted_at', null);

        $normalizedSearch = trim($search);

        if ($normalizedSearch !== '') {
            $query->groupStart()
                ->like('minishop_customers.name', $normalizedSearch)
                ->orLike('minishop_customers.phone', $normalizedSearch)
                ->orLike('minishop_customers.note', $normalizedSearch)
                ->groupEnd();
        }

        return $query->groupBy('minishop_customers.id')
            ->orderBy('minishop_customers.name', 'ASC')
            ->findAll();
    }

    public function findOneCustomerByBook(string $bookId, string $customerId): ?array
    {
        if ($bookId === '' || $customerId === '') {
            return null;
        }

        $customer = $this->select([
            'minishop_customers.id',
            'minishop_customers.book_id',
            'minishop_customers.created_by',
            'minishop_customers.name',
            'minishop_customers.phone',
            'minishop_customers.note',
            'minishop_customers.reminder_at',
            'minishop_customers.reminder_note',
            'minishop_customers.created_at',
            'minishop_customers.updated_at',
            'COUNT(minishop_sales.id) AS receipt_count',
            'COALESCE(SUM(minishop_sales.total_amount), 0) AS total_sales_amount',
            'COALESCE(SUM(minishop_sales.paid_amount), 0) AS total_paid_amount',
            'COALESCE(SUM(minishop_sales.due_amount), 0) AS outstanding_balance',
            'MAX(minishop_sales.sold_at) AS last_sale_at',
        ])->join(
            'minishop_sales',
            'minishop_sales.customer_id = minishop_customers.id'
            . ' AND minishop_sales.deleted_at IS NULL',
            'left'
        )->where('minishop_customers.id', $customerId)
            ->where('minishop_customers.book_id', $bookId)
            ->where('minishop_customers.deleted_at', null)
            ->groupBy('minishop_customers.id')
            ->first();

        return $customer ?: null;
    }

    /**
     * Book-scoped lookup for sale ownership and reminder updates.
     */
    public function findExistingByIdAndBook(string $bookId, string $customerId): ?array
    {
        if ($bookId === '' || $customerId === '') {
            return null;
        }

        $customer = $this->where('id', $customerId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $customer ?: null;
    }
}
