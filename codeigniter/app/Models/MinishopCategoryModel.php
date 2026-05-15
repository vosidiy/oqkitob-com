<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopCategoryModel extends Model
{
    protected $table            = 'minishop_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'id',
        'book_id',
        'created_by',
        'name',
        'sort_order',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps    = false;

    public function findByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'book_id',
            'created_by',
            'name',
            'sort_order',
            'created_at',
            'updated_at',
        ])->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function findExistingByIdAndBook(string $bookId, string $categoryId): ?array
    {
        if ($bookId === '' || $categoryId === '') {
            return null;
        }

        $category = $this->where('id', $categoryId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $category ?: null;
    }
}
