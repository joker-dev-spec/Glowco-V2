<?php
// --- config/payments.php ---
// Bank transfer details shown to customers at checkout.
// Edit these values if your account details change.

const PAYMENT_ACCOUNTS = [
    ['bank' => 'OPay',        'number' => '7043930307', 'name' => 'Kolawole Taiwo Rapheal'],
    ['bank' => 'Access Bank', 'number' => '1920600381', 'name' => 'Taiwo Rapheal'],
];

const FREE_SHIPPING_THRESHOLD = 15000.0;
const SHIPPING_FEE = 1500.0;

function get_bank_accounts(): array {
    return PAYMENT_ACCOUNTS;
}

function shipping_fee(float $subtotal): float {
    return $subtotal >= FREE_SHIPPING_THRESHOLD ? 0.0 : SHIPPING_FEE;
}
