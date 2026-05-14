<?php

namespace App\Models;

use CodeIgniter\Model;

class NoteModel extends Model
{
    protected $table          = 'notes';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = false;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'id',
        'book_id',
        'created_by',
        'title',
        'content',
        'color',
        'position',
        'is_pinned',
        'is_archived',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps  = false;

    public function findByBook(string $bookId): array
    {
        return $this->select([
            'id',
            'book_id',
            'created_by',
            'title',
            'content',
            'color',
            'position',
            'is_pinned',
            'is_archived',
            'created_at',
            'updated_at',
        ])->where('book_id', $bookId)
            ->where('is_archived', 0)
            ->where('deleted_at', null)
            ->orderBy('is_pinned', 'DESC')
            ->orderBy('position', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function findExistingByIdAndBook(string $bookId, string $noteId): ?array
    {
        if ($bookId === '' || $noteId === '') {
            return null;
        }

        $note = $this->select([
            'id',
            'book_id',
            'created_by',
            'title',
            'content',
            'color',
            'position',
            'is_pinned',
            'is_archived',
            'created_at',
            'updated_at',
            'deleted_at',
        ])->where('id', $noteId)
            ->where('book_id', $bookId)
            ->where('deleted_at', null)
            ->first();

        return $note ?: null;
    }
}
