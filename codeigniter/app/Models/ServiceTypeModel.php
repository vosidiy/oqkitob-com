<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceTypeModel extends Model
{
    private const LIST_COLUMNS = [
        'id',
        'book_id',
        'created_by',
        'name',
        'default_unit',
        'default_price',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $table            = 'app_service_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'id',
        'book_id',
        'created_by',
        'name',
        'default_unit',
        'default_price',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps    = false;

    /**
     * Returns the service catalog for the current book.
     */
    public function findByBook(string $bookId, bool $activeOnly = false): array
    {
        $query = $this->select(self::LIST_COLUMNS)
            ->where('book_id', $bookId)
            ->where('deleted_at', null);

        if ($activeOnly) {
            $query->where('is_active', 1);
        }

        return $query->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Book-scoped lookup for item snapshot and catalog updates.
     */
    public function findExistingByIdAndBook(string $bookId, string $serviceTypeId): ?array
    {
        if ($bookId === '' || $serviceTypeId === '') {
            return null;
        }

        $serviceType = $this->where('id', $serviceTypeId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $serviceType ?: null;
    }

    /**
     * Returns one list-shaped service type row after create requests.
     */
    public function findListRowByIdAndBook(string $bookId, string $serviceTypeId): ?array
    {
        if ($bookId === '' || $serviceTypeId === '') {
            return null;
        }

        $serviceType = $this->select(self::LIST_COLUMNS)
            ->where('id', $serviceTypeId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $serviceType ?: null;
    }

    /**
     * Returns the next sort_order value for a new service type in this book.
     */
    public function findNextSortOrderByBook(string $bookId): int
    {
        if ($bookId === '') {
            return 0;
        }

        $row = $this->builder()
            ->select('MAX(sort_order) AS max_sort_order', false)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_sort_order'] ?? -1)) + 1;
    }
}
