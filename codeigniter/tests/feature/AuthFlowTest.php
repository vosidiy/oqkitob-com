<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

final class AuthFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $passwordHash = '$2y$10$Gb2WOlLeeAg7j0fP2hNPke8dvXmuqUzb0y5iw0fZbyDW/aywm1vby';

    protected function setUp(): void
    {
        parent::setUp();

        config('Session')->driver = ArrayHandler::class;
        Services::reset();

        $db = db_connect('tests');

        $db->query('DROP TABLE IF EXISTS db_books');
        $db->query('DROP TABLE IF EXISTS db_users');

        $db->query(<<<'SQL'
CREATE TABLE db_users (
    id TEXT PRIMARY KEY,
    default_book_id TEXT NULL,
    name TEXT NULL,
    email TEXT NOT NULL,
    city TEXT NULL,
    country_name TEXT NULL,
    timezone TEXT NULL,
    password_hash TEXT NULL,
    locale TEXT NOT NULL DEFAULT 'en',
    status TEXT NOT NULL DEFAULT 'active',
    plan TEXT NOT NULL DEFAULT 'free',
    last_login_at TEXT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_books (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL,
    type_key TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT NULL,
    icon TEXT NULL,
    color TEXT NULL,
    is_archived INTEGER NOT NULL DEFAULT 0,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $this->seedUsers($db);
        $this->seedBooks($db);
    }

    public function testLoginSucceedsWithSeededCredentials(): void
    {
        $response = $this->withBodyFormat('json')
            ->post('auth/login', [
                'email' => 'ali@example.com',
                'password' => 'Demo123!',
            ]);

        $response->assertStatus(200);
        $response->assertSessionHas('user_id', '11111111-1111-1111-1111-111111111111');

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Login successful.', $payload['message']);
        self::assertSame('Ali Vohidov', $payload['user']['name']);
        self::assertArrayNotHasKey('password_hash', $payload['user']);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $response = $this->withBodyFormat('json')
            ->post('auth/login', [
                'email' => 'ali@example.com',
                'password' => 'WrongPassword1!',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Invalid email or password.', $payload['message']);
    }

    public function testLoginFailsForBlockedUser(): void
    {
        $response = $this->withBodyFormat('json')
            ->post('auth/login', [
                'email' => 'blocked@example.com',
                'password' => 'Demo123!',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('This account is not allowed to sign in.', $payload['message']);
    }

    public function testMeRequiresAuthentication(): void
    {
        $response = $this->get('auth/me');

        $response->assertStatus(401);
    }

    public function testStatusReturnsFalseForGuests(): void
    {
        $response = $this->get('auth/status');
        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertFalse($payload['logged_in']);
    }

    public function testStatusReturnsTrueForAuthenticatedUsers(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('auth/status');

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['logged_in']);
    }

    public function testBooksReturnsOnlyTheAuthenticatedUsersVisibleBooks(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books');

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $books = $payload['books'];

        self::assertCount(2, $books);
        self::assertSame('Daily Notes', $books[0]['title']);
        self::assertSame('Personal Tasks', $books[1]['title']);
    }

    public function testLogoutClearsTheSession(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->post('auth/logout');

        $response->assertStatus(200);
        $response->assertSessionMissing('user_id');

        $followUp = $this->withSession([])->get('auth/status');
        $followUp->assertStatus(200);

        $payload = json_decode((string) $followUp->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertFalse($payload['logged_in']);
    }

    public function testLogoutRemovesAccessToMe(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->post('auth/logout');

        $response->assertStatus(200);

        $followUp = $this->withSession([])->get('auth/me');
        $followUp->assertStatus(401);
    }

    private function seedUsers($db): void
    {
        $db->table('users')->insertBatch([
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'default_book_id' => 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
                'name' => 'Ali Vohidov',
                'email' => 'ali@example.com',
                'city' => 'Tashkent',
                'country_name' => 'Uzbekistan',
                'timezone' => 'Asia/Tashkent',
                'password_hash' => $this->passwordHash,
                'status' => 'active',
                'plan' => 'free',
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => '99999999-9999-9999-9999-999999999999',
                'default_book_id' => null,
                'name' => 'Blocked User',
                'email' => 'blocked@example.com',
                'city' => 'Tashkent',
                'country_name' => 'Uzbekistan',
                'timezone' => 'Asia/Tashkent',
                'password_hash' => $this->passwordHash,
                'status' => 'blocked',
                'plan' => 'free',
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
        ]);
    }

    private function seedBooks($db): void
    {
        $db->table('books')->insertBatch([
            [
                'id' => 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
                'user_id' => '11111111-1111-1111-1111-111111111111',
                'type_key' => 'notes',
                'title' => 'Daily Notes',
                'description' => 'Personal notes book for Ali',
                'icon' => null,
                'color' => null,
                'is_archived' => 0,
                'sort_order' => 1,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaa2',
                'user_id' => '11111111-1111-1111-1111-111111111111',
                'type_key' => 'todo',
                'title' => 'Personal Tasks',
                'description' => 'Task management book for Ali',
                'icon' => null,
                'color' => null,
                'is_archived' => 0,
                'sort_order' => 2,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'aaaaaaa3-aaaa-aaaa-aaaa-aaaaaaaaaaa3',
                'user_id' => '11111111-1111-1111-1111-111111111111',
                'type_key' => 'notes',
                'title' => 'Archived Notes',
                'description' => 'Archived book should be hidden',
                'icon' => null,
                'color' => null,
                'is_archived' => 1,
                'sort_order' => 3,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'bbbbbbb1-bbbb-bbbb-bbbb-bbbbbbbbbbb1',
                'user_id' => '22222222-2222-2222-2222-222222222222',
                'type_key' => 'notes',
                'title' => 'Someone Else Book',
                'description' => 'Should never be visible',
                'icon' => null,
                'color' => null,
                'is_archived' => 0,
                'sort_order' => 1,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
        ]);
    }
}
