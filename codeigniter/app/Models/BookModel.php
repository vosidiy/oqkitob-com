<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table          = 'books';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'user_id',
        'type_key',
        'title',
        'description',
        'icon',
        'color',
        'settings_json',
        'is_archived',
        'sort_order',
        'last_opened_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps  = false;

    public function findSidebarBooksForUser(string $userId): array
    {
        return $this->select([
            'id',
            'title',
            'type_key',
            'description',
            'icon',
            'color',
            'is_archived',
        ])->where('user_id', $userId)
            ->where('deleted_at', null)
            ->where('is_archived', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
