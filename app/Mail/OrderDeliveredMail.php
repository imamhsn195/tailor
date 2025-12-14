<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public Delivery $delivery;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, Delivery $delivery)
    {
        $this->order = $order;
        $this->delivery = $delivery;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Order Delivered - ' . $this->order->order_number)
            ->view('emails.orders.delivered')
            ->with([
                'order' => $this->order,
                'delivery' => $this->delivery,
                'customer' => $this->order->customer,
            ]);
    }
}


