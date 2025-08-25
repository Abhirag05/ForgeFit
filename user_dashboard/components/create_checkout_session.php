<?php
session_start();
require '../../vendor/autoload.php'; // adjust if you’re not using composer

\Stripe\Stripe::setApiKey('sk_test_51Rzs7eAGXSIrL9LVRHOyczb8HJrmvfuXOJXBcFJmaJrCYSB3CnmKJNPxabvWKu6tFtdGZH3OUfqi3D38HapmGjms00pBtZQ5Sq'); // replace with your Stripe Secret Key

header('Content-Type: application/json');

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'mode' => 'payment', // ✅ one-time payment, not subscription
        'line_items' => [[
            'price_data' => [
                'currency' => 'inr',
                'product_data' => [
                    'name' => 'ForgeFit Premium Access',
                ],
                'unit_amount' => 10000, // 100 INR = 100 * 100 paisa
            ],
            'quantity' => 1,
        ]],
        'success_url' => 'http://localhost/ForgeFit_mini_project/user_dashboard/components/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
'cancel_url' => 'http://localhost/ForgeFit_mini_project/user_dashboard/components/payment_cancel.php',
        'customer_email' => $_SESSION['email'] ?? null,
    ]);

    echo json_encode(['id' => $checkout_session->id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>