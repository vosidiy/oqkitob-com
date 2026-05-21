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
        $db->query('DROP TABLE IF EXISTS db_book_types');
        $db->query('DROP TABLE IF EXISTS db_users');

        $db->query(<<<'SQL'
CREATE TABLE db_users (
    id TEXT PRIMARY KEY,
    default_book_id TEXT NULL,
    name TEXT NULL,
    email TEXT NOT NULL,
    phone TEXT NULL,
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
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_book_types (
    type_key TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NULL,
    updated_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_notes (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NULL,
    title TEXT NULL,
    content TEXT NULL,
    color TEXT NULL,
    position INTEGER NOT NULL DEFAULT 0,
    is_pinned INTEGER NOT NULL DEFAULT 0,
    is_archived INTEGER NOT NULL DEFAULT 0,
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
        $this->seedBookTypes($db);
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

    public function testMeReturnsExpandedProfileForAuthenticatedUser(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('auth/me');

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Ali Vohidov', $payload['user']['name']);
        self::assertSame('ali@example.com', $payload['user']['email']);
        self::assertSame('+998900000111', $payload['user']['phone']);
        self::assertSame('2026-05-11 20:52:13', $payload['user']['created_at']);
        self::assertSame('2026-05-11 20:52:13', $payload['user']['updated_at']);
        self::assertArrayNotHasKey('password_hash', $payload['user']);
    }

    public function testProfileUpdateRequiresAuthentication(): void
    {
        $response = $this->withBodyFormat('json')
            ->put('auth/profile', [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(401);
    }

    public function testProfileUpdateCanChangeName(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/profile', [
                'name' => 'Updated Ali',
            ]);

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row = db_connect('tests')->table('users')
            ->where('id', '11111111-1111-1111-1111-111111111111')
            ->get()
            ->getRowArray();

        self::assertSame('Profile updated successfully.', $payload['message']);
        self::assertSame('Updated Ali', $payload['user']['name']);
        self::assertSame('Updated Ali', $row['name']);
    }

    public function testProfileUpdateCanChangePhone(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/profile', [
                'phone' => '+998901234567',
            ]);

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row = db_connect('tests')->table('users')
            ->where('id', '11111111-1111-1111-1111-111111111111')
            ->get()
            ->getRowArray();

        self::assertSame('Profile updated successfully.', $payload['message']);
        self::assertSame('+998901234567', $payload['user']['phone']);
        self::assertSame('+998901234567', $row['phone']);
    }

    public function testProfileUpdateCanClearPhoneWithBlankInput(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/profile', [
                'phone' => '   ',
            ]);

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row = db_connect('tests')->table('users')
            ->where('id', '11111111-1111-1111-1111-111111111111')
            ->get()
            ->getRowArray();

        self::assertNull($payload['user']['phone']);
        self::assertNull($row['phone']);
    }

    public function testProfileUpdateRejectsBlankName(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/profile', [
                'name' => '   ',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Name is required.', $payload['message']);
    }

    public function testProfileUpdateRejectsOverlongName(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/profile', [
                'name' => str_repeat('a', 256),
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Name must be 255 characters or fewer.', $payload['message']);
    }

    public function testProfileUpdateRejectsOverlongPhone(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/profile', [
                'phone' => str_repeat('1', 51),
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Phone must be 50 characters or fewer.', $payload['message']);
    }

    public function testProfileUpdateRejectsEmptyPayload(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/profile', []);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Please provide at least one profile field to update.', $payload['message']);
    }

    public function testPasswordUpdateRequiresAuthentication(): void
    {
        $response = $this->withBodyFormat('json')
            ->put('auth/password', [
                'current_password' => 'Demo123!',
                'new_password' => 'BetterPass123!',
                'new_password_confirmation' => 'BetterPass123!',
            ]);

        $response->assertStatus(401);
    }

    public function testPasswordUpdateRejectsWrongCurrentPassword(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/password', [
                'current_password' => 'WrongPassword1!',
                'new_password' => 'BetterPass123!',
                'new_password_confirmation' => 'BetterPass123!',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Current password is incorrect.', $payload['message']);
    }

    public function testPasswordUpdateRejectsSameAsCurrentPassword(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/password', [
                'current_password' => 'Demo123!',
                'new_password' => 'Demo123!',
                'new_password_confirmation' => 'Demo123!',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('New password must be different from the current password.', $payload['message']);
    }

    public function testPasswordUpdateRejectsMismatchedConfirmation(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/password', [
                'current_password' => 'Demo123!',
                'new_password' => 'BetterPass123!',
                'new_password_confirmation' => 'DifferentPass123!',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('New password confirmation does not match.', $payload['message']);
    }

    public function testPasswordUpdateSucceedsAndKeepsAuthentication(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/password', [
                'current_password' => 'Demo123!',
                'new_password' => 'BetterPass123!',
                'new_password_confirmation' => 'BetterPass123!',
            ]);

        $response->assertStatus(200);
        $response->assertSessionHas('user_id', '11111111-1111-1111-1111-111111111111');

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row = db_connect('tests')->table('users')
            ->where('id', '11111111-1111-1111-1111-111111111111')
            ->get()
            ->getRowArray();
        $followUp = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('auth/me');

        self::assertSame('Password updated successfully.', $payload['message']);
        self::assertTrue(password_verify('BetterPass123!', $row['password_hash']));
        self::assertFalse(password_verify('Demo123!', $row['password_hash']));
        $followUp->assertStatus(200);
    }

    public function testPasswordUpdateReplacesLoginCredentials(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('auth/password', [
                'current_password' => 'Demo123!',
                'new_password' => 'BetterPass123!',
                'new_password_confirmation' => 'BetterPass123!',
            ]);

        $response->assertStatus(200);

        $oldLogin = $this->withBodyFormat('json')
            ->post('auth/login', [
                'email' => 'ali@example.com',
                'password' => 'Demo123!',
            ]);
        $newLogin = $this->withBodyFormat('json')
            ->post('auth/login', [
                'email' => 'ali@example.com',
                'password' => 'BetterPass123!',
            ]);

        $oldLogin->assertStatus(422);
        $newLogin->assertStatus(200);
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

    public function testBookTypesRequireAuthentication(): void
    {
        $response = $this->get('books/types');

        $response->assertStatus(401);
    }

    public function testBookTypesReturnOnlyActiveTypesInAlphabeticalOrder(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/types');

        $response->assertStatus(200);

        $payload   = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $bookTypes = $payload['bookTypes'];

        self::assertCount(3, $bookTypes);
        self::assertSame('Finance', $bookTypes[0]['name']);
        self::assertSame('Notes', $bookTypes[1]['name']);
        self::assertSame('Todo', $bookTypes[2]['name']);
    }

    public function testCreateBookRequiresAuthentication(): void
    {
        $response = $this->withBodyFormat('json')
            ->post('books', [
                'title' => 'Travel Plans',
                'description' => 'Summer trip ideas',
                'type_key' => 'notes',
            ]);

        $response->assertStatus(401);
    }

    public function testCreateBookSucceedsForTheAuthenticatedUser(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->post('books', [
                'title' => 'Travel Plans',
                'description' => 'Summer trip ideas',
                'type_key' => 'notes',
            ]);

        $response->assertStatus(201);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $book    = $payload['book'];
        $row     = db_connect('tests')->table('books')
            ->where('id', $book['id'])
            ->get()
            ->getRowArray();

        self::assertSame('Book created successfully.', $payload['message']);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $book['id']
        );
        self::assertSame('Travel Plans', $book['title']);
        self::assertSame('notes', $book['type_key']);
        self::assertSame('Summer trip ideas', $book['description']);
        self::assertArrayNotHasKey('user_id', $book);

        self::assertNotNull($row);
        self::assertSame('11111111-1111-1111-1111-111111111111', $row['user_id']);
        self::assertSame('notes', $row['type_key']);
        self::assertSame('Travel Plans', $row['title']);
        self::assertSame('Summer trip ideas', $row['description']);
        self::assertSame(5, (int) $row['sort_order']);
        self::assertNotEmpty($row['created_at']);
        self::assertSame($row['created_at'], $row['updated_at']);
    }

    public function testCreateBookRejectsBlankTitle(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->post('books', [
                'title' => '   ',
                'description' => 'Summer trip ideas',
                'type_key' => 'notes',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Book name is required.', $payload['message']);
    }

    public function testCreateBookRejectsInvalidTypeKey(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->post('books', [
                'title' => 'Travel Plans',
                'description' => 'Summer trip ideas',
                'type_key' => 'unknown',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Please select a valid book type.', $payload['message']);
    }

    public function testCreateBookRejectsInactiveTypeKey(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->post('books', [
                'title' => 'Travel Plans',
                'description' => 'Summer trip ideas',
                'type_key' => 'legacy',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Please select a valid book type.', $payload['message']);
    }

    public function testNewlyCreatedBookAppearsAtTheEndOfTheSidebarList(): void
    {
        $createResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->post('books', [
                'title' => 'Trip Checklist',
                'description' => '',
                'type_key' => 'todo',
            ]);

        $createResponse->assertStatus(201);

        $listResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books');

        $listResponse->assertStatus(200);

        $payload = json_decode((string) $listResponse->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $books   = $payload['books'];
        $lastBook = $books[array_key_last($books)];

        self::assertCount(4, $books);
        self::assertSame('Trip Checklist', $lastBook['title']);
        self::assertSame('todo', $lastBook['type_key']);
        self::assertNull($lastBook['description']);
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
        self::assertSame('yellow', $payload['notes'][0]['color']);
    }

    public function testCreateNoteRequiresAuthentication(): void
    {
        $response = $this->withBodyFormat('json')
            ->post('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes', [
                'title' => 'New note',
                'content' => 'Fresh content',
                'color' => 'blue',
            ]);

        $response->assertStatus(401);
    }

    public function testCreateNoteSucceedsForAccessibleNotesBook(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->post('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes', [
                'title' => 'Travel ideas',
                'content' => 'Visit Samarkand',
                'color' => 'blue',
            ]);

        $response->assertStatus(201);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $note    = $payload['note'];
        $row     = db_connect('tests')->table('notes')
            ->where('id', $note['id'])
            ->get()
            ->getRowArray();

        self::assertSame('Note created successfully.', $payload['message']);
        self::assertSame('Travel ideas', $note['title']);
        self::assertSame('Visit Samarkand', $note['content']);
        self::assertSame('blue', $note['color']);
        self::assertSame('11111111-1111-1111-1111-111111111111', $note['created_by']);
        self::assertSame(0, (int) $note['is_pinned']);
        self::assertSame(0, (int) $note['is_archived']);
        self::assertNull($note['deleted_at']);

        self::assertNotNull($row);
        self::assertSame('aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1', $row['book_id']);
        self::assertSame('11111111-1111-1111-1111-111111111111', $row['created_by']);
        self::assertSame('blue', $row['color']);
        self::assertSame(0, (int) $row['is_archived']);
        self::assertNotEmpty($row['created_at']);
        self::assertSame($row['created_at'], $row['updated_at']);
    }

    public function testCreateNoteRejectsBlankTitleAndContent(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->post('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes', [
                'title' => '   ',
                'content' => '   ',
                'color' => '',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Please enter a title or note content.', $payload['message']);
    }

    public function testCreateNoteRejectsInvalidColor(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->post('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes', [
                'title' => 'Color test',
                'content' => '',
                'color' => 'purple',
            ]);

        $response->assertStatus(422);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Please choose a valid note color.', $payload['message']);
    }

    public function testUpdateNoteSucceedsForAccessibleNote(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes/note-2', [
                'title' => 'Updated second note',
                'content' => 'Updated content',
                'color' => 'green',
            ]);

        $response->assertStatus(200);

        $payload = json_decode((string) $response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row     = db_connect('tests')->table('notes')
            ->where('id', 'note-2')
            ->get()
            ->getRowArray();

        self::assertSame('Note updated successfully.', $payload['message']);
        self::assertSame('Updated second note', $payload['note']['title']);
        self::assertSame('Updated content', $payload['note']['content']);
        self::assertSame('green', $payload['note']['color']);
        self::assertSame('note-2', $payload['note']['id']);
        self::assertNotNull($row);
        self::assertSame('Updated second note', $row['title']);
        self::assertSame('Updated content', $row['content']);
        self::assertSame('green', $row['color']);
        self::assertSame($row['updated_at'], $payload['note']['updated_at']);
    }

    public function testArchiveNoteHidesItFromNotesListWithoutDeletingIt(): void
    {
        $archiveResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->post('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes/note-2/archive');

        $archiveResponse->assertStatus(200);

        $payload = json_decode((string) $archiveResponse->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row     = db_connect('tests')->table('notes')
            ->where('id', 'note-2')
            ->get()
            ->getRowArray();

        self::assertSame('Note archived successfully.', $payload['message']);
        self::assertSame('note-2', $payload['noteId']);
        self::assertSame(1, (int) $payload['is_archived']);
        self::assertNotNull($row);
        self::assertSame(1, (int) $row['is_archived']);
        self::assertNull($row['deleted_at']);
        self::assertSame($row['updated_at'], $payload['updated_at']);

        $listResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes');

        $listResponse->assertStatus(200);

        $listPayload = json_decode((string) $listResponse->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $noteIds     = array_column($listPayload['notes'], 'id');

        self::assertNotContains('note-2', $noteIds);
    }

    public function testPinNoteMarksTheNoteAsPinnedAndKeepsItFirst(): void
    {
        $pinResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->post('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes/note-2/pin');

        $pinResponse->assertStatus(200);

        $payload = json_decode((string) $pinResponse->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row     = db_connect('tests')->table('notes')
            ->where('id', 'note-2')
            ->get()
            ->getRowArray();

        self::assertSame('Note pinned successfully.', $payload['message']);
        self::assertSame('note-2', $payload['noteId']);
        self::assertSame(1, (int) $payload['is_pinned']);
        self::assertNotNull($row);
        self::assertSame(1, (int) $row['is_pinned']);
        self::assertSame($row['updated_at'], $payload['updated_at']);

        $listResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes');

        $listResponse->assertStatus(200);

        $listPayload = json_decode((string) $listResponse->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Pinned Note', $listPayload['notes'][0]['title']);
        self::assertSame('Second Note', $listPayload['notes'][1]['title']);
        self::assertSame(1, (int) $listPayload['notes'][1]['is_pinned']);
    }

    public function testUnpinNoteClearsThePinnedFlag(): void
    {
        $unpinResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->post('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes/note-1/unpin');

        $unpinResponse->assertStatus(200);

        $payload = json_decode((string) $unpinResponse->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row     = db_connect('tests')->table('notes')
            ->where('id', 'note-1')
            ->get()
            ->getRowArray();

        self::assertSame('Note unpinned successfully.', $payload['message']);
        self::assertSame('note-1', $payload['noteId']);
        self::assertSame(0, (int) $payload['is_pinned']);
        self::assertNotNull($row);
        self::assertSame(0, (int) $row['is_pinned']);
        self::assertSame($row['updated_at'], $payload['updated_at']);
    }

    public function testDeleteNoteSoftDeletesItAndHidesItFromNotesList(): void
    {
        $deleteResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->delete('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes/note-2');

        $deleteResponse->assertStatus(200);

        $payload = json_decode((string) $deleteResponse->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $row     = db_connect('tests')->table('notes')
            ->where('id', 'note-2')
            ->get()
            ->getRowArray();

        self::assertSame('Note deleted successfully.', $payload['message']);
        self::assertSame('note-2', $payload['noteId']);
        self::assertNotNull($row);
        self::assertNotEmpty($row['deleted_at']);
        self::assertSame($row['deleted_at'], $payload['deleted_at']);
        self::assertSame($row['updated_at'], $payload['updated_at']);

        $listResponse = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->get('books/aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1/notes');

        $listResponse->assertStatus(200);

        $listPayload = json_decode((string) $listResponse->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        $noteIds     = array_column($listPayload['notes'], 'id');

        self::assertNotContains('note-2', $noteIds);
    }

    public function testUpdateNoteRejectsInaccessibleBook(): void
    {
        $response = $this->withSession([
            'user_id' => '11111111-1111-1111-1111-111111111111',
        ])->withBodyFormat('json')
            ->put('books/bbbbbbb1-bbbb-bbbb-bbbb-bbbbbbbbbbb1/notes/note-3', [
                'title' => 'Should fail',
                'content' => 'Should fail',
                'color' => '',
            ]);

        $response->assertStatus(404);
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
                'phone' => '+998900000111',
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
                'phone' => '+998900000222',
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
                'phone' => '+998900000999',
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

    private function seedBookTypes($db): void
    {
        $db->table('book_types')->insertBatch([
            [
                'type_key' => 'finance',
                'name' => 'Finance',
                'description' => 'Book type for income and expense tracking',
                'is_active' => 1,
                'created_at' => '2026-05-11 20:01:14',
                'updated_at' => '2026-05-11 20:01:14',
            ],
            [
                'type_key' => 'notes',
                'name' => 'Notes',
                'description' => 'Book type for note taking',
                'is_active' => 1,
                'created_at' => '2026-05-11 20:01:14',
                'updated_at' => '2026-05-11 20:01:14',
            ],
            [
                'type_key' => 'todo',
                'name' => 'Todo',
                'description' => 'Book type for task management',
                'is_active' => 1,
                'created_at' => '2026-05-11 20:01:14',
                'updated_at' => '2026-05-11 20:01:14',
            ],
            [
                'type_key' => 'legacy',
                'name' => 'Legacy',
                'description' => 'Inactive book type for validation tests',
                'is_active' => 0,
                'created_at' => '2026-05-11 20:01:14',
                'updated_at' => '2026-05-11 20:01:14',
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
                'created_by' => '11111111-1111-1111-1111-111111111111',
                'title' => 'Pinned Note',
                'content' => 'First note content',
                'color' => 'yellow',
                'position' => 0,
                'is_pinned' => 1,
                'is_archived' => 0,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'note-2',
                'book_id' => 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
                'created_by' => '11111111-1111-1111-1111-111111111111',
                'title' => 'Second Note',
                'content' => 'Second note content',
                'color' => null,
                'position' => 1,
                'is_pinned' => 0,
                'is_archived' => 0,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'note-archived',
                'book_id' => 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
                'created_by' => '11111111-1111-1111-1111-111111111111',
                'title' => 'Archived Note',
                'content' => 'Should stay hidden',
                'color' => 'red',
                'position' => 2,
                'is_pinned' => 0,
                'is_archived' => 1,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => null,
            ],
            [
                'id' => 'note-deleted',
                'book_id' => 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
                'created_by' => '11111111-1111-1111-1111-111111111111',
                'title' => 'Deleted Note',
                'content' => 'Should stay hidden',
                'color' => null,
                'position' => 3,
                'is_pinned' => 0,
                'is_archived' => 0,
                'created_at' => '2026-05-11 20:52:13',
                'updated_at' => '2026-05-11 20:52:13',
                'deleted_at' => '2026-05-12 20:52:13',
            ],
            [
                'id' => 'note-3',
                'book_id' => 'bbbbbbb1-bbbb-bbbb-bbbb-bbbbbbbbbbb1',
                'created_by' => '22222222-2222-2222-2222-222222222222',
                'title' => 'Other User Note',
                'content' => 'Hidden from Ali',
                'color' => 'blue',
                'position' => 0,
                'is_pinned' => 0,
                'is_archived' => 0,
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
