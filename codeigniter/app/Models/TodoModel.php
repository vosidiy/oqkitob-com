<?php

namespace App\Models;

use CodeIgniter\Model;

class TodoModel extends Model
{
    protected $table          = 'todos';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = false;

    public function findByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'book_id',
            'parent_id',
            'title',
            'description',
            'priority',
            'is_completed',
            'due_at',
            'completed_at',
            'position',
            'created_at',
            'updated_at',
        ])->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('is_completed', 'ASC')
            ->orderBy('position', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
