<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Created</title>
</head>
<body>
    <h2>Order Created</h2>
    <p>Dear {{ $customer->name }},</p>
    <p>Your order has been created successfully.</p>
    <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
    <p><strong>Order Date:</strong> {{ $order->order_date->format('Y-m-d') }}</p>
    <p><strong>Delivery Date:</strong> {{ $order->delivery_date->format('Y-m-d') }}</p>
    <p><strong>Total Amount:</strong> {{ currency_format($order->net_payable) }}</p>
    <p>Thank you for your business!</p>
</body>
</html>


