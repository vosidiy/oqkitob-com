<?php

namespace App\Models;

use CodeIgniter\Model;

class NoteModel extends Model
{
    protected $table          = 'notes';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = false;

    public function findByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'book_id',
            'title',
            'content',
            'position',
            'is_pinned',
            'created_at',
            'updated_at',
        ])->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->orderBy('is_pinned', 'DESC')
            ->orderBy('position', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
