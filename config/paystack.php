<?php


define('PAYSTACK_SECRET_KEY', 'sk_test_REPLACE_WITH_REAL_KEY');

function initialize_paystack_transaction(string $email, float $amount, int $order_id): array {
    $url = "https://api.paystack.co/transaction/initialize";

    $fields = [
        'email' => $email,
        'amount' => (int)($amount * 100),
        'callback_url' => BASE_URL . "cart/verify_payment.php",
        'metadata' => ['order_id' => $order_id]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['data'] ?? ['authorization_url' => BASE_URL . "cart/checkout_failed.php"];
}