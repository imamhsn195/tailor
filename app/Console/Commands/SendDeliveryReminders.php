<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Jobs\SendSMS;
use App\Jobs\SendEmail;
use App\Mail\DeliveryReminderMail;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendDeliveryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send-delivery-reminders 
                            {--days=1 : Number of days before delivery to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send delivery reminder SMS and emails to customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $reminderDate = Carbon::today()->addDays($days);

        $orders = Order::where('status', '!=', 'delivered')
            ->where('status', '!=', 'cancelled')
            ->whereDate('delivery_date', $reminderDate)
            ->with(['customer'])
            ->get();

        $this->info("Found {$orders->count()} orders for delivery reminder on {$reminderDate->format('Y-m-d')}");

        foreach ($orders as $order) {
            if (!$order->customer) {
                continue;
            }

            $customer = $order->customer;

            // Send SMS
            if ($customer->mobile) {
                $smsMessage = str_replace(
                    ['{customer_name}', '{order_number}', '{delivery_date}'],
                    [
                        $customer->name,
                        $order->order_number,
                        $order->delivery_date->format('Y-m-d'),
                    ],
                    config('sms.templates.delivery_reminder', 'Order #{order_number} scheduled for delivery on {delivery_date}. Please be available.')
                );

                SendSMS::dispatch($customer->mobile, $smsMessage);
                $this->info("SMS reminder sent to {$customer->mobile} for order {$order->order_number}");
            }

            // Send Email
            if ($customer->email) {
                SendEmail::dispatch(
                    $customer->email,
                    'Delivery Reminder - Order #' . $order->order_number,
                    'emails.orders.delivery_reminder',
                    ['order' => $order, 'customer' => $customer]
                );
                $this->info("Email reminder sent to {$customer->email} for order {$order->order_number}");
            }
        }

        $this->info('Delivery reminders sent successfully!');
        return 0;
    }
}


