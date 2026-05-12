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

        $db->query('DROP TABLE IF EXISTS db_finance_transactions');
        $db->query('DROP TABLE IF EXISTS db_todos');
        $db->query('DROP TABLE IF EXISTS db_notes');
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

        $db->query(<<<'SQL'
CREATE TABLE db_notes (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    title TEXT NULL,
    content TEXT NULL,
    position INTEGER NOT NULL DEFAULT 0,
    is_pinned INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_todos (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    parent_id TEXT NULL,
    title TEXT NOT NULL,
    description TEXT NULL,
    priority TEXT NOT NULL DEFAULT 'medium',
    is_completed INTEGER NOT NULL DEFAULT 0,
    due_at TEXT NULL,
    completed_at TEXT NULL,
    position INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_finance_transactions (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    category_id TEXT NULL,
    type TEXT NOT NULL,
    amount TEXT NOT NULL,
    currency_code TEXT NOT NULL,
    transaction_date TEXT NOT NULL,
    note TEXT NULL,
    reference TEXT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $this->seedUsers($db);
        $this->seedBooks($db);
        $this->seedNotes($db);
        $this->seedTodos($db);
        $this->seedTransactions($db);
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

    public function testBooksReturnsOnlyTheAuthenticatedUsersVisibleBooks(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books');

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $books   = $payload['books'];

        self::assertCount(3, $books);
        self::assertSame('Daily Notes', $books[0]['title']);
        self::assertSame('Personal Tasks', $books[1]['title']);
        self::assertSame('Personal Finance', $books[2]['title']);
    }

    public function testNotesEndpointRequiresAuthentication(): void
    {
        $response = $this->get('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes');

        $response->assertStatus(401);
    }

    public function testNotesEndpointReturnsOnlySelectedBooksNotes(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes');

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload['notes']);
        self::assertSame('Pinned Note', $payload['notes'][0]['title']);
        self::assertSame('Second Note', $payload['notes'][1]['title']);
    }

    public function testTodosEndpointReturnsOnlySelectedBooksTodos(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaa2/todos');

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload['todos']);
        self::assertSame('Pay internet bill', $payload['todos'][0]['title']);
        self::assertSame('Buy groceries', $payload['todos'][1]['title']);
    }

    public function testFinanceEndpointReturnsSelectedBooksTransactions(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/aaaaaaa4-aaaa-aaaa-aaaa-aaaaaaaaaaa4/finance');

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload['transactions']);
        self::assertSame('expense', $payload['transactions'][0]['type']);
        self::assertSame('income', $payload['transactions'][1]['type']);
    }

    public function testBookTypeEndpointsRejectInaccessibleBooks(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/bbbbbbb1-bbbb-bbbb-bbbb-bbbbbbbbbbb1/notes');

        $response->assertStatus(404);
    }

    public function testBookTypeEndpointsRejectWrongBookType(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaa2/notes');

        $response->assertStatus(404);
    }

    public function testLogoutClearsTheSession(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->post('auth/logout');

        $response->assertStatus(200);
        $response->assertSessionMissing('user_id');
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
                'id' => '22222222-2222-2222-2222-222222222222',
                'default_book_id' => 'bbbbbbb1-bbbb-bbbb-bbbb-bbbbbbbbbbb1',
                'name' => 'Malika Karimova',
                'email' => 'malika@example.com',
                'city' => 'Samarkand',
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
                'id' => 'aaaaaaa4-aaaa-aaaa-aaaa-aaaaaaaaaaa4',
                'user_id' => '11111111-1111-1111-1111-111111111111',
                'type_key' => 'finance',
                'title' => 'Personal Finance',
                'description' => 'Finance tracking book for Ali',
                'icon' => null,
                'color' => null,
                'is_archived' => 0,
                'sort_order' => 3,
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
                'sort_order' => 4,
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

    private function seedNotes($db): void
    {
        $db->table('notes')->insertBatch([
            [
                'id' => 'note-1',
                'book_id' => 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
                'title' => 'Pinned Note',
                'content' => 'First note content',
                'position' => 0,
                'is_pinned' => 1,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'note-2',
                'book_id' => 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
                'title' => 'Second Note',
                'content' => 'Second note content',
                'position' => 1,
                'is_pinned' => 0,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'note-3',
                'book_id' => 'bbbbbbb1-bbbb-bbbb-bbbb-bbbbbbbbbbb1',
                'title' => 'Other User Note',
                'content' => 'Hidden from Ali',
                'position' => 0,
                'is_pinned' => 0,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
        ]);
    }

    private function seedTodos($db): void
    {
        $db->table('todos')->insertBatch([
            [
                'id' => 'todo-1',
                'book_id' => 'aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaa2',
                'parent_id' => null,
                'title' => 'Pay internet bill',
                'description' => 'Complete today',
                'priority' => 'high',
                'is_completed' => 0,
                'due_at' => null,
                'completed_at' => null,
                'position' => 0,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'todo-2',
                'book_id' => 'aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaa2',
                'parent_id' => null,
                'title' => 'Buy groceries',
                'description' => 'Milk and bread',
                'priority' => 'medium',
                'is_completed' => 1,
                'due_at' => null,
                'completed_at' => '2026-05-11 20:52:13',
                'position' => 1,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
        ]);
    }

    private function seedTransactions($db): void
    {
        $db->table('finance_transactions')->insertBatch([
            [
                'id' => 'tx-1',
                'book_id' => 'aaaaaaa4-aaaa-aaaa-aaaa-aaaaaaaaaaa4',
                'category_id' => null,
                'type' => 'income',
                'amount' => '2000.00',
                'currency_code' => 'USD',
                'transaction_date' => '2026-05-10',
                'note' => 'Salary',
                'reference' => null,
                'created_at' => '2026-05-10 20:52:13',
                'updated_at' => '2026-05-10 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'tx-2',
                'book_id' => 'aaaaaaa4-aaaa-aaaa-aaaa-aaaaaaaaaaa4',
                'category_id' => null,
                'type' => 'expense',
                'amount' => '25.00',
                'currency_code' => 'USD',
                'transaction_date' => '2026-05-11',
                'note' => 'Lunch',
                'reference' => null,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
        ]);
    }
}
