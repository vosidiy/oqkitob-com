<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * Shared base for authenticated JSON endpoints.
 *
 * Use this in API controllers that already sit behind AuthFilter and need
 * read access to the current session user ID. Public controllers such as
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
     * Returns the authenticated user ID and then releases the session lock.
     *
     * Book read endpoints use this before database queries so parallel SPA
     * requests do not block each other on the session handler.
     */
    protected function currentUserIdForRead(): string
    {
        $userId = $this->currentUserId();

        // Release the session lock before query work so concurrent SPA reads do not block.
        $this->session->close();

        return $userId;
    }
}
