<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Minimal session-based guard for authenticated API routes.
 *
 * This filter should stay lightweight: it only answers whether `user_id`
 * exists in the CI4 session. Controllers then use AuthenticatedApiController
 * to read that same value safely.
 */
class AuthFilter implements FilterInterface
{
    use ResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
    {
        // Use the framework session service only. Controllers rely on the same
        // session key, so auth behavior stays consistent across the app.
        $session = service('session');
        $userId  = $session->get('user_id');

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
