<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Reminder</title>
</head>
<body>
    <h2>Delivery Reminder</h2>
    <p>Dear {{ $customer->name }},</p>
    <p>This is a reminder that your order is scheduled for delivery.</p>
    <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
    <p><strong>Scheduled Delivery Date:</strong> {{ $order->delivery_date->format('Y-m-d') }}</p>
    <p>Please be available for delivery.</p>
    <p>Thank you!</p>
</body>
</html>


