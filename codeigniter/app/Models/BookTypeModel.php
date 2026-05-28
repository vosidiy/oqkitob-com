<?php

namespace App\Models;

use CodeIgniter\Model;

class BookTypeModel extends Model
{
    protected $table            = 'book_types';
    protected $primaryKey       = 'type_key';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'type_key',
        'name',
        'description',
        'requires_currency',
        'is_active',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;

    /**
     * Lightweight projection for the create-book dialog radio buttons.
     */
    public function findActiveForSelection(): array
    {
        return $this->select([
            'type_key',
            'name',
            'description',
            'requires_currency',
        ])->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->orderBy('type_key', 'ASC')
            ->findAll();
    }

    /**
     * Returns one active book type row for create-flow capability checks.
     *
     * BooksController::create() uses this so validation is based on the same
     * active type metadata the frontend create dialog renders.
     */
    public function findActiveByTypeKey(string $typeKey): ?array
    {
        if ($typeKey === '') {
            return null;
        }

        $bookType = $this->select([
            'type_key',
            'name',
            'description',
            'requires_currency',
        ])
            ->where('type_key', $typeKey)
            ->where('is_active', 1)
            ->first();

        return $bookType ?: null;
    }

    /**
     * Returns one book type row even if it is no longer active.
     */
    public function findByTypeKey(string $typeKey): ?array
    {
        if ($typeKey === '') {
            return null;
        }

        $bookType = $this->select([
            'type_key',
            'name',
            'description',
            'requires_currency',
        ])->where('type_key', $typeKey)
            ->first();

        return $bookType ?: null;
    }

    /**
     * Returns a compact lookup set for multiple book types.
     *
     * @param list<string> $typeKeys
     * @return array<int, array<string, mixed>>
     */
    public function findByTypeKeys(array $typeKeys): array
    {
        $normalizedTypeKeys = array_values(array_filter(array_map(static fn ($typeKey) => trim((string) $typeKey), $typeKeys)));

        if ($normalizedTypeKeys === []) {
            return [];
        }

        return $this->select([
            'type_key',
            'name',
            'description',
            'requires_currency',
        ])->whereIn('type_key', array_values(array_unique($normalizedTypeKeys)))
            ->findAll();
    }
}
