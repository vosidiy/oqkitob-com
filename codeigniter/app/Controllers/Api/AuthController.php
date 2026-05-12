<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\I18n\Time;
use RuntimeException;

class AuthController extends BaseController
{
    use ResponseTrait;

    public function __construct(
        private readonly UserModel $users = new UserModel()
    ) {
    }

    public function login()
    {
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $email   = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            return $this->respond([
                'message' => 'Email and password are required.',
            ], 422);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->respond([
                'message' => 'Please enter a valid email address.',
            ], 422);
        }

        try {
            $user = $this->attemptLogin($email, $password);
        } catch (RuntimeException $exception) {
            return $this->respond([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return $this->respond([
            'message' => 'Login successful.',
            'user'    => $user,
        ]);
    }

    public function logout()
    {
        $session = service('session');
        $session->start();
        $session->remove('user_id');

        return $this->respond([
            'message' => 'Logout successful.',
        ]);
    }

    public function me()
    {
        $session = service('session');
        $session->start();

        $userId = (string) $session->get('user_id');
        $user   = $this->users->getProfileById($userId);

        if (! $user) {
            return $this->respond([
                'message' => 'Authentication required.',
            ], 401);
        }

        return $this->respond([
            'user' => $user,
        ]);
    }

    private function attemptLogin(string $email, string $password): array
    {
        $user = $this->users->findActiveByEmail(trim(strtolower($email)));

        if (! $user || empty($user['password_hash']) || ! password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('Invalid email or password.');
        }

        if (($user['status'] ?? null) !== 'active') {
            throw new RuntimeException('This account is not allowed to sign in.');
        }

        $session = service('session');
        $session->start();
        $session->set('user_id', $user['id']);

        $this->users->update($user['id'], [
            'last_login_at' => Time::now('UTC')->toDateTimeString(),
        ]);

        $profile = $this->users->getProfileById($user['id']);

        if (! $profile) {
            throw new RuntimeException('Unable to load user profile.');
        }

        return $profile;
    }
}
