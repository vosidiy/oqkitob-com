<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'default_book_id',
        'name',
        'email',
        'phone',
        'country_name',
        'country_code',
        'city',
        'timezone',
        'date_of_birth',
        'email_verified_at',
        'password_hash',
        'google_id',
        'avatar',
        'locale',
        'status',
        'last_login_at',
        'plan',
        'license_expires_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps    = false;

    public function findActiveByEmail(string $email): ?array
    {
        $user = $this->where('email', $email)
            ->where('deleted_at', null)
            ->first();

        return $user ?: null;
    }

    public function getProfileById(string $userId): ?array
    {
        $user = $this->select([
            'id',
            'name',
            'email',
            'phone',
            'city',
            'country_name',
            'timezone',
            'plan',
            'created_at',
            'updated_at',
        ])->where('id', $userId)
            ->where('deleted_at', null)
            ->first();

        return $user ?: null;
    }

    public function getAuthUserById(string $userId): ?array
    {
        $user = $this->select([
            'id',
            'password_hash',
            'status',
        ])->where('id', $userId)
            ->where('deleted_at', null)
            ->first();

        return $user ?: null;
    }
}
