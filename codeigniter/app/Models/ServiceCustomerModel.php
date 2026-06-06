<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceCustomerModel extends Model
{
    private const LIST_COLUMNS = [
        'id',
        'book_id',
        'created_by',
        'name',
        'phone',
        'messenger',
        'address',
        'location',
        'created_at',
        'updated_at',
    ];

    protected $table            = 'app_service_customers';
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
        'messenger',
        'address',
        'location',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps    = false;

    /**
     * Basic customer list for the current service book.
     */
    public function findByBook(string $bookId): array
    {
        return $this->select(self::LIST_COLUMNS)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Full customer summary list for the current service book.
     */
    public function findCustomerListByBook(string $bookId, string $search = ''): array
    {
        $query = $this->select([
            'app_service_customers.id',
            'app_service_customers.book_id',
            'app_service_customers.created_by',
            'app_service_customers.name',
            'app_service_customers.phone',
            'app_service_customers.messenger',
            'app_service_customers.address',
            'app_service_customers.location',
            'app_service_customers.created_at',
            'app_service_customers.updated_at',
            'COUNT(app_service_orders.id) AS order_count',
            'MAX(COALESCE(app_service_orders.received_at, app_service_orders.created_at)) AS last_order_at',
        ])->join(
            'app_service_orders',
            'app_service_orders.customer_id = app_service_customers.id'
            . ' AND app_service_orders.deleted_at IS NULL',
            'left'
        )->where('app_service_customers.book_id', $bookId)
            ->where('app_service_customers.deleted_at', null);

        $normalizedSearch = trim($search);

        if ($normalizedSearch !== '') {
            $query->groupStart()
                ->like('app_service_customers.name', $normalizedSearch)
                ->orLike('app_service_customers.phone', $normalizedSearch)
                ->groupEnd();
        }

        return $query->groupBy('app_service_customers.id')
            ->orderBy('last_order_at', 'DESC')
            ->orderBy('app_service_customers.updated_at', 'DESC')
            ->orderBy('app_service_customers.name', 'ASC')
            ->findAll();
    }

    /**
     * Full customer detail summary for the current service book.
     */
    public function findOneCustomerByBook(string $bookId, string $customerId): ?array
    {
        if ($bookId === '' || $customerId === '') {
            return null;
        }

        $customer = $this->select([
            'app_service_customers.id',
            'app_service_customers.book_id',
            'app_service_customers.created_by',
            'app_service_customers.name',
            'app_service_customers.phone',
            'app_service_customers.messenger',
            'app_service_customers.address',
            'app_service_customers.location',
            'app_service_customers.created_at',
            'app_service_customers.updated_at',
            'COUNT(app_service_orders.id) AS order_count',
            'MAX(COALESCE(app_service_orders.received_at, app_service_orders.created_at)) AS last_order_at',
        ])->join(
            'app_service_orders',
            'app_service_orders.customer_id = app_service_customers.id'
            . ' AND app_service_orders.deleted_at IS NULL',
            'left'
        )->where('app_service_customers.id', $customerId)
            ->where('app_service_customers.book_id', $bookId)
            ->where('app_service_customers.deleted_at', null)
            ->groupBy('app_service_customers.id')
            ->first();

        return $customer ?: null;
    }

    /**
     * Lightweight customer options for order pickers.
     */
    public function findOptionsByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'name',
            'phone',
            'messenger',
        ])->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Book-scoped lookup for order ownership checks and updates.
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

    /**
     * Exact phone lookup for reusing customers during order creation.
     * When duplicates exist, the most recently updated record wins.
     */
    public function findExistingByPhoneAndBook(string $bookId, string $phone): ?array
    {
        if ($bookId === '' || $phone === '') {
            return null;
        }

        $customer = $this->where('book_id', $bookId)
            ->where('phone', $phone)
            ->where('deleted_at', null)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();

        return $customer ?: null;
    }
}
