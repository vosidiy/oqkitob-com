<?php

namespace App\Models;

use CodeIgniter\Model;

class MinishopCategoryModel extends Model
{
    private const SELECTION_COLUMNS = [
        'id',
        'name',
    ];

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

    /**
     * Returns active categories in the order the UI will usually render them.
     */
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

    /**
     * Book-scoped lookup so callers cannot accidentally cross books.
     */
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

    /**
     * Book-scoped lookup by category name for inline product-create flows.
     */
    public function findExistingByNameAndBook(string $bookId, string $name): ?array
    {
        $normalizedName = trim($name);

        if ($bookId === '' || $normalizedName === '') {
            return null;
        }

        $category = $this->where('book_id', $bookId)
            ->where('name', $normalizedName)
            ->where('deleted_at', null)
            ->first();

        return $category ?: null;
    }

    /**
     * Loads active categories by ids for a specific book.
     *
     * @param list<string> $categoryIds
     * @return array<int, array<string, mixed>>
     */
    public function findExistingByIdsAndBook(string $bookId, array $categoryIds): array
    {
        $normalizedIds = array_values(array_filter(array_map(static fn ($categoryId) => trim((string) $categoryId), $categoryIds)));

        if ($bookId === '' || $normalizedIds === []) {
            return [];
        }

        return $this->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->whereIn('id', $normalizedIds)
            ->findAll();
    }

    /**
     * Lightweight category options for product-create forms.
     */
    public function findSelectionByBook(string $bookId): array
    {
        return $this->select(self::SELECTION_COLUMNS)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
