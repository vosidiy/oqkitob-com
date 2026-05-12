<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    use ResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (! is_string($userId) || $userId === '') {
            $session = service('session');
            $session->start();
            $userId = $session->get('user_id');
        }

        if (! is_string($userId) || $userId === '') {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'message' => 'Authentication required.',
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
