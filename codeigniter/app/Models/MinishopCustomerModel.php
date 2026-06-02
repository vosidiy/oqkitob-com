<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopCustomerModel extends Model
{
    protected $table            = 'app_minishop_customers';
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
     * Lightweight customer options for searchable sale pickers.
     */
    public function findOptionsByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'name',
            'phone',
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
            'app_minishop_customers.id',
            'app_minishop_customers.book_id',
            'app_minishop_customers.created_by',
            'app_minishop_customers.name',
            'app_minishop_customers.phone',
            'app_minishop_customers.note',
            'app_minishop_customers.reminder_at',
            'app_minishop_customers.reminder_note',
            'app_minishop_customers.created_at',
            'app_minishop_customers.updated_at',
            'COUNT(app_minishop_sales.id) AS receipt_count',
            'COALESCE(SUM(app_minishop_sales.total_amount), 0) AS total_sales_amount',
            'COALESCE(SUM(app_minishop_sales.paid_amount), 0) AS total_paid_amount',
            'COALESCE(SUM(app_minishop_sales.due_amount), 0) AS outstanding_balance',
        ])->join(
            'app_minishop_sales',
            'app_minishop_sales.customer_id = app_minishop_customers.id'
            . ' AND app_minishop_sales.deleted_at IS NULL',
            'left'
        )->where('app_minishop_customers.book_id', $bookId)
            ->where('app_minishop_customers.deleted_at', null);

        $normalizedSearch = trim($search);

        if ($normalizedSearch !== '') {
            $query->groupStart()
                ->like('app_minishop_customers.name', $normalizedSearch)
                ->orLike('app_minishop_customers.phone', $normalizedSearch)
                ->orLike('app_minishop_customers.note', $normalizedSearch)
                ->groupEnd();
        }

        return $query->groupBy('app_minishop_customers.id')
            ->orderBy('app_minishop_customers.name', 'ASC')
            ->findAll();
    }

    public function findOneCustomerByBook(string $bookId, string $customerId): ?array
    {
        if ($bookId === '' || $customerId === '') {
            return null;
        }

        $customer = $this->select([
            'app_minishop_customers.id',
            'app_minishop_customers.book_id',
            'app_minishop_customers.created_by',
            'app_minishop_customers.name',
            'app_minishop_customers.phone',
            'app_minishop_customers.note',
            'app_minishop_customers.reminder_at',
            'app_minishop_customers.reminder_note',
            'app_minishop_customers.created_at',
            'app_minishop_customers.updated_at',
            'COUNT(app_minishop_sales.id) AS receipt_count',
            'COALESCE(SUM(app_minishop_sales.total_amount), 0) AS total_sales_amount',
            'COALESCE(SUM(app_minishop_sales.paid_amount), 0) AS total_paid_amount',
            'COALESCE(SUM(app_minishop_sales.due_amount), 0) AS outstanding_balance',
        ])->join(
            'app_minishop_sales',
            'app_minishop_sales.customer_id = app_minishop_customers.id'
            . ' AND app_minishop_sales.deleted_at IS NULL',
            'left'
        )->where('app_minishop_customers.id', $customerId)
            ->where('app_minishop_customers.book_id', $bookId)
            ->where('app_minishop_customers.deleted_at', null)
            ->groupBy('app_minishop_customers.id')
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
