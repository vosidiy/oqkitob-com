<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BookModel;
use CodeIgniter\API\ResponseTrait;

class BooksController extends BaseController
{
    use ResponseTrait;

    public function __construct(
        private readonly BookModel $books = new BookModel()
    ) {
    }

    public function index()
    {
        $userId = (string) ($_SESSION['user_id'] ?? '');

        if ($userId === '') {
            $session = service('session');
            $session->start();
            $userId = (string) $session->get('user_id');
        }

        return $this->respond([
            'books' => $this->books->findSidebarBooksForUser($userId),
        ]);
    }
}
