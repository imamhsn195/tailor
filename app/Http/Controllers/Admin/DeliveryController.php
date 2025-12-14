<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Jobs\SendSMS;
use App\Jobs\SendEmail;
use App\Mail\OrderDeliveredMail;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:delivery.view')->only(['index', 'show']);
        $this->middleware('permission:delivery.create')->only(['create', 'store']);
        $this->middleware('permission:delivery.edit')->only(['edit', 'update']);
        $this->middleware('permission:delivery.delete')->only(['destroy']);
    }

    /**
     * Display a listing of deliveries
     */
    public function index(Request $request)
    {
        $this->authorize('view', Delivery::class);

        $query = Delivery::with(['order.customer', 'order.branch', 'user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('delivery_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('delivery_date', '<=', $request->date_to);
        }

        $deliveries = $query->latest('delivery_date')->paginate(15);

        return view('admin.deliveries.index', compact('deliveries'));
    }

    /**
     * Show the form for creating a new delivery
     */
    public function create(Request $request)
    {
        $this->authorize('create', Delivery::class);

        $orderId = $request->input('order_id');
        $order = $orderId ? Order::with(['customer', 'items.product'])->findOrFail($orderId) : null;
        $orders = Order::where('status', '!=', 'delivered')
            ->with('customer')
            ->latest('order_date')
            ->get();

        return view('admin.deliveries.create', compact('order', 'orders'));
    }

    /**
     * Store a newly created delivery
     */
    public function store(Request $request)
    {
        $this->authorize('create', Delivery::class);

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_date' => 'required|date',
            'delivered_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($validated['order_id']);

            $delivery = Delivery::create([
                'order_id' => $validated['order_id'],
                'delivery_date' => $validated['delivery_date'],
                'delivered_amount' => $validated['delivered_amount'],
                'payment_method' => $validated['payment_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Update order paid amount and status
            $order->increment('paid_amount', $validated['delivered_amount']);
            $order->decrement('due_amount', $validated['delivered_amount']);

            if ($order->due_amount <= 0) {
                $order->update(['status' => 'delivered']);
            } else {
                $order->update(['status' => 'in_progress']);
            }

            DB::commit();

            // Send delivery notification
            $this->sendDeliveryNotification($order, $delivery);

            return redirect()->route('admin.deliveries.show', $delivery)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified delivery
     */
    public function show(Delivery $delivery)
    {
        $this->authorize('view', $delivery);

        $delivery->load(['order.customer', 'order.branch', 'order.items.product', 'user']);

        return view('admin.deliveries.show', compact('delivery'));
    }

    /**
     * Send delivery notification (SMS and Email)
     */
    protected function sendDeliveryNotification(Order $order, Delivery $delivery): void
    {
        $order->load(['customer']);
        $customer = $order->customer;

        if (!$customer) {
            return;
        }

        // Send SMS
        if ($customer->mobile) {
            $smsMessage = str_replace(
                ['{customer_name}', '{order_number}'],
                [$customer->name, $order->order_number],
                config('sms.templates.order_delivered', 'Order #{order_number} has been delivered. Thank you!')
            );

            SendSMS::dispatch($customer->mobile, $smsMessage);
        }

        // Send Email
        if ($customer->email) {
            SendEmail::dispatch(
                $customer->email,
                'Order Delivered - ' . $order->order_number,
                'emails.orders.delivered',
                ['order' => $order, 'delivery' => $delivery, 'customer' => $customer]
            );
        }
    }
}


