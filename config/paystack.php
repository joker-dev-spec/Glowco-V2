<?php
// --- config/paystack.php ---

define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY') ?: 'sk_test_REPLACE_WITH_REAL_KEY');
define('PAYSTACK_PUBLIC_KEY', getenv('PAYSTACK_PUBLIC_KEY') ?: 'pk_test_REPLACE_WITH_REAL_KEY');

/**
 * Perform an HTTP request to the Paystack API.
 * Uses cURL when available, otherwise falls back to stream contexts.
 *
 * @return array{status: int, body: string}
 */
function paystack_http_request(string $method, string $url, array $headers = [], ?string $postFields = null): array {
    $headers[] = "Authorization: Bearer " . PAYSTACK_SECRET_KEY;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            error_log("Paystack HTTP error: $err");
            return ['status' => 0, 'body' => ''];
        }
        return ['status' => $code, 'body' => $body];
    }

    $context = stream_context_create([
        'http' => [
            'method'  => $method,
            'header'  => "Content-Type: application/json\r\n" . implode("\r\n", $headers) . "\r\n",
            'content' => $postFields ?? '',
            'timeout' => 30,
            'ignore_errors' => true,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return ['status' => 0, 'body' => ''];
    }

    $status = 200;
    if (isset($http_response_header[0]) && preg_match('#HTTP/\d(?:\.\d)?\s+(\d+)#', $http_response_header[0], $m)) {
        $status = (int)$m[1];
    }
    return ['status' => $status, 'body' => $body];
}

function initialize_paystack_transaction(string $email, float $amount, int $order_id): array {
    $url = "https://api.paystack.co/transaction/initialize";

    $fields = [
        'email'        => $email,
        'amount'       => (int)($amount * 100),
        'callback_url' => BASE_URL . "cart/verify_payment.php",
        'metadata'     => ['order_id' => $order_id]
    ];

    $res = paystack_http_request(
        'POST',
        $url,
        ["Content-Type: application/json"],
        json_encode($fields)
    );

    if ($res['status'] === 0 || $res['status'] >= 400 || $res['body'] === '') {
        error_log("Paystack init failed: HTTP {$res['status']} — {$res['body']}");
        return ['authorization_url' => BASE_URL . "cart/cart.php"];
    }

    $data = json_decode($res['body'], true);
    return $data['data'] ?? ['authorization_url' => BASE_URL . "cart/cart.php"];
}

function verify_paystack_transaction(string $ref): ?array {
    $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($ref);

    $res = paystack_http_request('GET', $url);

    if ($res['status'] === 0 || $res['status'] >= 400 || $res['body'] === '') {
        error_log("Paystack verify failed: HTTP {$res['status']} — {$res['body']}");
        return null;
    }

    $data = json_decode($res['body'], true);
    return $data['data'] ?? null;
}
