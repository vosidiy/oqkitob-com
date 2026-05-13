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
        ])->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->orderBy('type_key', 'ASC')
            ->findAll();
    }

    /**
     * Create requests only allow currently active book types.
     */
    public function isActiveTypeKey(string $typeKey): bool
    {
        if ($typeKey === '') {
            return false;
        }

        return $this->select('type_key')
            ->where('type_key', $typeKey)
            ->where('is_active', 1)
            ->first() !== null;
    }
}
