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
}
