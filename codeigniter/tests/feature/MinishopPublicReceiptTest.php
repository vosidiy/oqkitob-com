<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

final class MinishopPublicReceiptTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const BOOK_ID = '90000000-0000-0000-0000-000000000001';
    private const SALE_ID = '90000000-0000-0000-0000-000000000002';

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset();

        $db = db_connect('tests');

        $db->query('DROP TABLE IF EXISTS db_minishop_sale_payments');
        $db->query('DROP TABLE IF EXISTS db_minishop_sale_items');
        $db->query('DROP TABLE IF EXISTS db_minishop_sales');
        $db->query('DROP TABLE IF EXISTS db_minishop_customers');
        $db->query('DROP TABLE IF EXISTS db_books');

        $db->query(<<<'SQL'
CREATE TABLE db_books (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL,
    type_key TEXT NOT NULL,
    currency_code TEXT NULL,
    title TEXT NOT NULL,
    description TEXT NULL,
    icon TEXT NULL,
    color TEXT NULL,
    settings_json TEXT NULL,
    is_archived INTEGER NOT NULL DEFAULT 0,
    sort_order INTEGER NOT NULL DEFAULT 0,
    last_opened_at TEXT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_minishop_customers (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NULL,
    name TEXT NOT NULL,
    phone TEXT NULL,
    note TEXT NULL,
    reminder_at TEXT NULL,
    reminder_note TEXT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_minishop_sales (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NULL,
    customer_id TEXT NULL,
    currency_code TEXT NOT NULL,
    subtotal_amount TEXT NOT NULL,
    discount_amount TEXT NOT NULL,
    total_amount TEXT NOT NULL,
    paid_amount TEXT NOT NULL,
    due_amount TEXT NOT NULL,
    payment_status TEXT NOT NULL,
    note TEXT NULL,
    sold_at TEXT NOT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_minishop_sale_items (
    id TEXT PRIMARY KEY,
    sale_id TEXT NOT NULL,
    product_id TEXT NULL,
    product_name TEXT NOT NULL,
    product_sku TEXT NULL,
    quantity TEXT NOT NULL,
    unit_price TEXT NOT NULL,
    line_total TEXT NOT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_minishop_sale_payments (
    id TEXT PRIMARY KEY,
    sale_id TEXT NOT NULL,
    created_by TEXT NULL,
    currency_code TEXT NOT NULL,
    amount TEXT NOT NULL,
    payment_method TEXT NOT NULL,
    paid_at TEXT NOT NULL,
    note TEXT NULL,
    created_at TEXT NULL
)
SQL);

        $this->seedReceiptFixture();
    }

    public function testPublicReceiptHtmlReturnsReceiptWithoutPhoneOrNotes(): void
    {
        $response = $this->get('public/books/' . self::BOOK_ID . '/minishop/sales/' . self::SALE_ID . '/receipt');
        $response->assertStatus(200);

        $body = $response->response()->getBody();

        self::assertStringContainsString('Customer Example', $body);
        self::assertStringContainsString('Notebook', $body);
        self::assertStringContainsString('Платежи', $body);
        self::assertStringContainsString('12 500', $body);
        self::assertStringNotContainsString('+998901234567', $body);
        self::assertStringNotContainsString('Private sale note', $body);
        self::assertStringNotContainsString('Private payment note', $body);
    }

    public function testPublicReceiptPdfReturnsPdfHeadersAndExcludesPrivateText(): void
    {
        $response = $this->get('public/books/' . self::BOOK_ID . '/minishop/sales/' . self::SALE_ID . '/receipt.pdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="receipt-' . self::SALE_ID . '.pdf"');
        self::assertStringContainsString('application/pdf', $response->response()->getHeaderLine('Content-Type'));

        $body = $response->response()->getBody();

        self::assertStringStartsWith('%PDF-', $body);
        self::assertStringNotContainsString('+998901234567', $body);
        self::assertStringNotContainsString('Private sale note', $body);
        self::assertStringNotContainsString('Private payment note', $body);
    }

    public function testPublicReceiptReturnsNotFoundForMissingSale(): void
    {
        $response = $this->get('public/books/' . self::BOOK_ID . '/minishop/sales/missing-sale/receipt');
        $response->assertStatus(404);
    }

    public function testPublicReceiptReturnsNotFoundForMissingBook(): void
    {
        $response = $this->get('public/books/missing-book/minishop/sales/' . self::SALE_ID . '/receipt.pdf');
        $response->assertStatus(404);
    }

    private function seedReceiptFixture(): void
    {
        $db = db_connect('tests');

        $db->table('books')->insert([
            'id' => self::BOOK_ID,
            'user_id' => '90000000-0000-0000-0000-000000000010',
            'type_key' => 'minishop',
            'currency_code' => 'UZS',
            'title' => 'Shop Book',
            'description' => null,
            'icon' => null,
            'color' => null,
            'settings_json' => '{"money_display":{"show_cents":false,"thousand_separator":"space"}}',
            'is_archived' => 0,
            'sort_order' => 1,
            'last_opened_at' => null,
            'created_at' => '2026-06-01 09:00:00',
            'updated_at' => '2026-06-01 09:00:00',
            'deleted_at' => null,
        ]);

        $db->table('minishop_customers')->insert([
            'id' => '90000000-0000-0000-0000-000000000003',
            'book_id' => self::BOOK_ID,
            'created_by' => null,
            'name' => 'Customer Example',
            'phone' => '+998901234567',
            'note' => 'Private customer note',
            'reminder_at' => null,
            'reminder_note' => null,
            'created_at' => '2026-06-01 09:05:00',
            'updated_at' => '2026-06-01 09:05:00',
            'deleted_at' => null,
        ]);

        $db->table('minishop_sales')->insert([
            'id' => self::SALE_ID,
            'book_id' => self::BOOK_ID,
            'created_by' => null,
            'customer_id' => '90000000-0000-0000-0000-000000000003',
            'currency_code' => 'UZS',
            'subtotal_amount' => '15000.00',
            'discount_amount' => '2500.00',
            'total_amount' => '12500.00',
            'paid_amount' => '10000.00',
            'due_amount' => '2500.00',
            'payment_status' => 'partial',
            'note' => 'Private sale note',
            'sold_at' => '2026-06-01 12:45:00',
            'created_at' => '2026-06-01 12:45:00',
            'updated_at' => '2026-06-01 12:45:00',
            'deleted_at' => null,
        ]);

        $db->table('minishop_sale_items')->insertBatch([
            [
                'id' => '90000000-0000-0000-0000-000000000004',
                'sale_id' => self::SALE_ID,
                'product_id' => null,
                'product_name' => 'Notebook',
                'product_sku' => 'NB-1',
                'quantity' => '1.000',
                'unit_price' => '10000.00',
                'line_total' => '10000.00',
            ],
            [
                'id' => '90000000-0000-0000-0000-000000000005',
                'sale_id' => self::SALE_ID,
                'product_id' => null,
                'product_name' => 'Pen',
                'product_sku' => 'PEN-1',
                'quantity' => '1.000',
                'unit_price' => '5000.00',
                'line_total' => '5000.00',
            ],
        ]);

        $db->table('minishop_sale_payments')->insert([
            'id' => '90000000-0000-0000-0000-000000000006',
            'sale_id' => self::SALE_ID,
            'created_by' => null,
            'currency_code' => 'UZS',
            'amount' => '10000.00',
            'payment_method' => 'cash',
            'paid_at' => '2026-06-01 12:45:00',
            'note' => 'Private payment note',
            'created_at' => '2026-06-01 12:45:00',
        ]);
    }
}
