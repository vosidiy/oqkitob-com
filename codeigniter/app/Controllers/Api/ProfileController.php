<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use App\Support\AuthRules;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authenticated profile management endpoints for the web SPA.
 *
 * Routes:
 * PUT /api/auth/profile
 * PUT /api/auth/password
 */
class ProfileController extends AuthenticatedApiController
{
    public function __construct(
        private readonly UserModel $users = new UserModel()
    ) {
    }

    public function updateProfile()
    {
        $userId = $this->currentUserIdAndCloseSession();

        if ($this->users->getProfileById($userId) === null) {
            return $this->respond([
                'message' => 'Authentication required.',
            ], 401);
        }

        try {
            $profile = $this->saveProfile($userId, $this->getProfilePayload());
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to update profile right now.');
        }

        return $this->respond([
            'message' => 'Profile updated successfully.',
            'user' => $profile,
        ]);
    }

    public function updatePassword()
    {
        $userId = $this->currentUserId();
        $user = $this->users->getAuthUserById($userId);

        if ($user === null) {
            return $this->respond([
                'message' => 'Authentication required.',
            ], 401);
        }

        try {
            $this->savePassword($userId, $user, $this->getPasswordPayload());
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to update password right now.');
        }

        return $this->respond([
            'message' => 'Password updated successfully.',
        ]);
    }

    private function getProfilePayload(): array
    {
        $payload = $this->getRequestPayload();
        $hasName = array_key_exists('name', $payload);
        $hasPhone = array_key_exists('phone', $payload);

        if (! $hasName && ! $hasPhone) {
            throw new InvalidArgumentException('Please provide at least one profile field to update.');
        }

        $normalized = [
            'has_name' => $hasName,
            'has_phone' => $hasPhone,
            'name' => $hasName ? trim((string) $payload['name']) : null,
            'phone' => null,
            'phone_is_invalid' => false,
        ];

        if ($hasPhone) {
            helper('phone');
            $phone = trim((string) ($payload['phone'] ?? ''));
            $normalized['phone'] = $phone !== '' ? normalize_phone_e164($phone) : null;
            $normalized['phone_is_invalid'] = $phone !== '' && $normalized['phone'] === null;
        }

        return $normalized;
    }

    private function getPasswordPayload(): array
    {
        $payload = $this->getRequestPayload();

        return [
            'current_password' => (string) ($payload['current_password'] ?? ''),
            'new_password' => (string) ($payload['new_password'] ?? ''),
            'new_password_confirmation' => (string) ($payload['new_password_confirmation'] ?? ''),
        ];
    }

    private function getRequestPayload(): array
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getRawInput();
        }

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        return is_array($payload) ? $payload : [];
    }

    private function saveProfile(string $userId, array $payload): array
    {
        $this->validateProfilePayload($userId, $payload);

        $updateData = [
            'updated_at' => Time::now('UTC')->toDateTimeString(),
        ];

        if ($payload['has_name']) {
            $updateData['name'] = $payload['name'];
        }

        if ($payload['has_phone']) {
            $updateData['phone'] = $payload['phone'];
        }

        $updated = $this->users->update($userId, $updateData);

        if ($updated === false) {
            throw new RuntimeException('Unable to update profile right now.');
        }

        $profile = $this->users->getProfileById($userId);

        if ($profile === null) {
            throw new RuntimeException('Unable to load the updated profile right now.');
        }

        return $profile;
    }

    private function validateProfilePayload(string $userId, array $payload): void
    {
        if ($payload['has_name']) {
            $name = (string) $payload['name'];

            if ($name === '') {
                throw new InvalidArgumentException('Name is required.');
            }

            if (mb_strlen($name) > 255) {
                throw new InvalidArgumentException('Name must be 255 characters or fewer.');
            }
        }

        if ($payload['has_phone']) {
            $phone = $payload['phone'];

            if ($payload['phone_is_invalid']) {
                throw new InvalidArgumentException('Please enter a valid international phone number.');
            }

            if ($phone !== null && mb_strlen((string) $phone) > 50) {
                throw new InvalidArgumentException('Phone must be 50 characters or fewer.');
            }

            if ($phone !== null && $this->users->phoneExists($phone, $userId)) {
                throw new InvalidArgumentException('This phone number is already registered.');
            }
        }
    }

    private function savePassword(string $userId, array $user, array $payload): void
    {
        $this->validatePasswordPayload($user, $payload);

        $updated = $this->users->update($userId, [
            'password_hash' => password_hash($payload['new_password'], PASSWORD_DEFAULT),
            'updated_at' => Time::now('UTC')->toDateTimeString(),
        ]);

        if ($updated === false) {
            throw new RuntimeException('Unable to update password right now.');
        }

        // Rotate the session ID after a password change while keeping the user authenticated.
        $this->session->regenerate(true);
        $this->session->set('user_id', $userId);
    }

    private function validatePasswordPayload(array $user, array $payload): void
    {
        $currentPassword = $payload['current_password'];
        $newPassword = $payload['new_password'];
        $confirmation = $payload['new_password_confirmation'];

        if ($currentPassword === '' || $newPassword === '' || $confirmation === '') {
            throw new InvalidArgumentException('Current password, new password, and confirmation are required.');
        }

        if (mb_strlen($currentPassword) > 255) {
            throw new InvalidArgumentException('Passwords must be 255 characters or fewer.');
        }

        if (empty($user['password_hash']) || ! password_verify($currentPassword, $user['password_hash'])) {
            throw new InvalidArgumentException('Current password is incorrect.');
        }

        if ($currentPassword === $newPassword) {
            throw new InvalidArgumentException('New password must be different from the current password.');
        }

        $passwordError = AuthRules::validateNewPassword($newPassword, $confirmation);

        if ($passwordError !== null) {
            throw new InvalidArgumentException($passwordError);
        }
    }
}
