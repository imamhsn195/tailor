<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Order Created - ' . $this->order->order_number)
            ->view('emails.orders.created')
            ->with([
                'order' => $this->order,
                'customer' => $this->order->customer,
                'branch' => $this->order->branch,
            ]);
    }
}


