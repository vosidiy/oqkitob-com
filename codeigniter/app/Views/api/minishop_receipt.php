<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title><?= esc('Чек ' . ($sale['id'] ?? '')) ?></title>
  <style>
    body {
      margin: 0;
      padding: <?= !empty($isPdf) ? '12px' : '24px' ?>;
      background: <?= !empty($isPdf) ? '#ffffff' : '#f5f7fb' ?>;
      color: #111827;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 16px;
      line-height: 1.3;
    }
    .receipt {
      max-width: 620px;
      margin: 0 auto;
      padding: 16px;
      background: #ffffff;
      border: 1px solid #dbe2ea;
      border-radius: 12px;
    }
    h1 {
      margin: 0 0 18px;
      font-size: 26px;
    }
    h2 {
      margin: 24px 0 12px;
      font-size: 18px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th,
    td {
      padding: 10px 12px;
      border-bottom: 1px solid #e5e7eb;
      vertical-align: top;
    }
    th {
      background: #f9fafb;
      font-weight: 700;
      text-align: left;
    }
    .summary td:first-child {
      width: 34%;
      color: #4b5563;
    }
    .meta-line {
      margin: 0 0 6px;
    }
    .text-right {
      text-align: right;
    }
    .muted {
      color: #6b7280;
    }
    .success {
      color: #166534;
      font-weight: 700;
    }
    .warning {
      color: #b45309;
      font-weight: 700;
    }
    .section-gap {
      margin-top: 24px;
    }
    small {
      color: #6b7280;
      font-size: 12px;
    }
  </style>
</head>
<body>
<?php
$separatorLookup = [
    'comma' => ',',
    'dot' => '.',
    'space' => ' ',
];
$thousandsSeparator = $separatorLookup[$moneyDisplay['thousand_separator'] ?? 'comma'] ?? ',';
$decimals = ($moneyDisplay['show_cents'] ?? true) === false ? 0 : 2;
$formatMoney = static function ($value) use ($thousandsSeparator, $decimals): string {
    $amount = is_numeric($value) ? (float) $value : 0.0;

    return number_format($amount, $decimals, '.', $thousandsSeparator);
};
$formatQuantity = static function ($value): string {
    $quantity = is_numeric($value) ? (float) $value : 0.0;
    $formatted = number_format($quantity, 3, '.', '');

    return rtrim(rtrim($formatted, '0'), '.');
};
$formatDateTime = static function ($value): string {
    $timestamp = strtotime((string) $value);

    return $timestamp !== false ? date('Y-m-d H:i', $timestamp) : (string) $value;
};
$paymentMethodLabels = [
    'cash' => 'Наличные',
    'card' => 'Карта',
];
$paymentStatusLabels = [
    'unpaid' => 'Не оплачено',
    'partial' => 'Частичная оплата',
    'paid' => 'Полная оплата',
];
$currencyCode = trim((string) ($sale['currency_code'] ?? ''));
$dueAmount = is_numeric($sale['due_amount'] ?? null) ? (float) $sale['due_amount'] : 0.0;
?>
  <div class="receipt">
    <h1>Чек</h1>
    <p class="meta-line">Номер чека: <strong><?= esc($sale['id'] ?? '') ?></strong></p>
    <p class="meta-line">Дата: <strong><?= esc($formatDateTime($sale['sold_at'] ?? '')) ?></strong></p>
    <?php if (! empty($sale['customer_name'])): ?>
      <p class="meta-line">Клиент: <strong><?= esc($sale['customer_name']) ?></strong></p>
    <?php endif; ?>
    <?php if ($currencyCode !== ''): ?>
      <p class="meta-line">Валюта: <strong><?= esc($currencyCode) ?></strong></p>
    <?php endif; ?>

    <h2>Товары</h2>
    <table>
      <thead>
        <tr>
          <th>Товар</th>
          <th>Кол-во</th>
          <th class="text-right">Цена</th>
          <th class="text-right">Сумма</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td> <strong><?= esc($item['product_name'] ?? '') ?></strong></td>
            <td><?= esc($formatQuantity($item['quantity'] ?? 0)) ?></td>
            <td class="text-right">
              <?= esc($formatMoney($item['unit_price'] ?? 0)) ?>
              <?php if ($currencyCode !== ''): ?><small><?= esc($currencyCode) ?></small><?php endif; ?>
            </td>
            <td class="text-right">
              <?= esc($formatMoney($item['line_total'] ?? 0)) ?>
              <?php if ($currencyCode !== ''): ?><small><?= esc($currencyCode) ?></small><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <hr>
    <div>
      <table class="summary">
        <tbody>
          <tr>
            <td>Подытог</td>
            <td class="text-right">
              <strong><?= esc($formatMoney($sale['subtotal_amount'] ?? 0)) ?></strong>
              <?php if ($currencyCode !== ''): ?><small><?= esc($currencyCode) ?></small><?php endif; ?>
            </td>
          </tr>
          <tr>
            <td>Скидка</td>
            <td class="text-right">
              <strong>- <?= esc($formatMoney($sale['discount_amount'] ?? 0)) ?></strong>
              <?php if ($currencyCode !== ''): ?><small><?= esc($currencyCode) ?></small><?php endif; ?>
            </td>
          </tr>
          <tr>
            <td>Итого</td>
            <td class="text-right">
              <strong><?= esc($formatMoney($sale['total_amount'] ?? 0)) ?></strong>
              <?php if ($currencyCode !== ''): ?><small><?= esc($currencyCode) ?></small><?php endif; ?>
            </td>
          </tr>
          <tr>
            <td>Оплачено</td>
            <td class="text-right success">
              <strong><?= esc($formatMoney($sale['paid_amount'] ?? 0)) ?></strong>
              <?php if ($currencyCode !== ''): ?><small><?= esc($currencyCode) ?></small><?php endif; ?>
            </td>
          </tr>
          <?php if ($dueAmount > 0): ?>
            <tr>
              <td>Долг</td>
              <td class="text-right warning">
                <strong><?= esc($formatMoney($sale['due_amount'] ?? 0)) ?></strong>
                <?php if ($currencyCode !== ''): ?><small><?= esc($currencyCode) ?></small><?php endif; ?>
              </td>
            </tr>
            <tr>
              <td>Статус</td>
              <td class="text-right warning">
                <strong><?= esc($paymentStatusLabels[$sale['payment_status'] ?? 'partial'] ?? 'Частичная оплата') ?></strong>
              </td>
            </tr>
          <?php else: ?>
            <tr>
              <td>Статус</td>
              <td class="text-right success">
                <strong>Полная оплата</strong>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <h2>Платежи</h2>
    <?php if ($payments === []): ?>
      <p class="muted">Платежей нет.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Дата</th>
            <th>Способ</th>
            <th class="text-right">Сумма</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment): ?>
            <tr>
              <td><?= esc($formatDateTime($payment['paid_at'] ?? $payment['created_at'] ?? '')) ?></td>
              <td><?= esc($paymentMethodLabels[$payment['payment_method'] ?? 'cash'] ?? 'Наличные') ?></td>
              <td class="text-right">
                <strong><?= esc($formatMoney($payment['amount'] ?? 0)) ?></strong>
                <?php if ($currencyCode !== ''): ?><small><?= esc($currencyCode) ?></small><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
