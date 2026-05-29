<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Support\AuthRules;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use RuntimeException;

/**
 * Public authentication endpoints for the web SPA.
 *
 * Unlike book-content controllers, this controller extends BaseController
 * directly because login/logout own the session lifecycle instead of assuming
 * AuthFilter has already granted access.
 */
class AuthController extends BaseController
{
    use ResponseTrait;

    public function __construct(
        private readonly UserModel $users = new UserModel()
    ) {
    }

    public function login()
    {
        // Accept JSON for SPA requests and fall back to form payloads for
        // simple/manual testing.
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $phone    = (string) ($payload['phone'] ?? '');
        $password = (string) ($payload['password'] ?? '');

        if ($phone === '' || $password === '') {
            return $this->respond([
                'message' => 'Phone and password are required.',
            ], 422);
        }

        helper('phone');
        $normalizedPhone = normalize_phone_e164($phone);

        if ($normalizedPhone === null) {
            return $this->respond([
                'message' => 'Please enter a valid international phone number.',
            ], 422);
        }

        try {
            $user = $this->attemptLogin($normalizedPhone, $password);
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

    public function register()
    {
        try {
            $user = $this->registerUser($this->getRegisterPayload());
        } catch (InvalidArgumentException $exception) {
            return $this->respond([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to create account right now.');
        }

        return $this->respond([
            'message' => 'Registration successful.',
            'user' => $user,
        ], 201);
    }

    public function logout()
    {
        // End the authenticated session completely so the browser receives a
        // fresh anonymous session ID after logout.
        $this->session->destroy();

        return $this->respond([
            'message' => 'Logout successful.',
        ]);
    }

    public function me()
    {
        $userId = (string) $this->session->get('user_id');

        // This is a read-only endpoint, so release the session lock before
        // loading the profile row from the database.
        $this->session->close();
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

    /**
     * Validates credentials, writes the authenticated session user ID, and
     * returns the sanitized profile that the SPA stores as auth state.
     */
    private function attemptLogin(string $phone, string $password): array
    {
        $user = $this->users->findActiveByPhone($phone);

        if (! $user || empty($user['password_hash']) || ! password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('Invalid phone or password.');
        }

        if (($user['status'] ?? null) !== 'active') {
            throw new RuntimeException('This account is not allowed to sign in.');
        }

        return $this->promoteAuthenticatedUser($user['id']);
    }

    private function getRegisterPayload(): array
    {
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        helper('phone');

        $phone = normalize_phone_e164((string) ($payload['phone'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $passwordConfirmation = (string) ($payload['password_confirmation'] ?? '');

        if ($phone === null) {
            throw new InvalidArgumentException('Please enter a valid international phone number.');
        }

        if ($name === '') {
            throw new InvalidArgumentException('Full name is required.');
        }

        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Full name must be 255 characters or fewer.');
        }

        $passwordError = AuthRules::validateNewPassword($password, $passwordConfirmation);

        if ($passwordError !== null) {
            throw new InvalidArgumentException($passwordError);
        }

        return [
            'phone' => $phone,
            'name' => $name,
            'password' => $password,
        ];
    }

    private function registerUser(array $payload): array
    {
        if ($this->users->phoneExists($payload['phone'])) {
            throw new InvalidArgumentException('This phone number is already registered.');
        }

        helper('uuid');
        $userId = uuid_v4();
        $timestamp = $this->utcNow();

        $created = $this->users->insert([
            'id' => $userId,
            'name' => $payload['name'],
            'phone' => $payload['phone'],
            'email' => null,
            'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
            'status' => 'active',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($created === false) {
            throw new RuntimeException('Unable to create account right now.');
        }

        return $this->promoteAuthenticatedUser($userId);
    }

    private function promoteAuthenticatedUser(string $userId): array
    {
        // Rotate the session ID before promoting the visitor into an
        // authenticated session to reduce fixation risk.
        $this->session->regenerate(true);

        // Controllers and AuthFilter both use this same session key later.
        $this->session->set('user_id', $userId);

        $this->users->update($userId, [
            'last_login_at' => Time::now('UTC')->toDateTimeString(),
        ]);

        $profile = $this->users->getProfileById($userId);

        if (! $profile) {
            throw new RuntimeException('Unable to load user profile.');
        }

        return $profile;
    }
}
