<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Delivered</title>
</head>
<body>
    <h2>Order Delivered</h2>
    <p>Dear {{ $customer->name }},</p>
    <p>Your order has been delivered successfully.</p>
    <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
    <p><strong>Delivery Date:</strong> {{ $delivery->delivery_date->format('Y-m-d') }}</p>
    <p><strong>Delivered Amount:</strong> {{ currency_format($delivery->delivered_amount) }}</p>
    <p>Thank you for your business!</p>
</body>
</html>


