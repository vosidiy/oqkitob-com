<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * Shared base for authenticated JSON endpoints.
 *
 * Use this in API controllers that already sit behind AuthFilter and need
 * access to the current session user ID. Public controllers such as
 * AuthController should continue extending BaseController directly.
 */
abstract class AuthenticatedApiController extends BaseController
{
    use ResponseTrait;

    /**
     * Returns the authenticated user ID from the CI4 session service.
     *
     * Call this when the request still needs to keep the session open for
     * further session writes.
     */
    protected function currentUserId(): string
    {
        return (string) $this->session->get('user_id');
    }

    /**
     * Returns the authenticated user ID and releases the session lock.
     *
     * Use this for authenticated endpoints that do not need later session writes.
     */
    protected function currentUserIdAndCloseSession(): string
    {
        $userId = $this->currentUserId();

        $this->session->close();

        return $userId;
    }
}
