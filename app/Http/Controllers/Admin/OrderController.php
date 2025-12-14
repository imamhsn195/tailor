<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Product;
use App\Models\MeasurementTemplate;
use App\Services\SMSService;
use App\Jobs\SendSMS;
use App\Jobs\SendEmail;
use App\Mail\OrderCreatedMail;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:order.view')->only(['index', 'show']);
        $this->middleware('permission:order.create')->only(['create', 'store']);
        $this->middleware('permission:order.edit')->only(['edit', 'update']);
        $this->middleware('permission:order.delete')->only(['destroy']);
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $this->authorize('view', Order::class);

        $query = Order::with(['customer', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
        }

        $orders = $query->latest('order_date')->paginate(15);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.orders.index', compact('orders', 'branches'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        $this->authorize('create', Order::class);

        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $templates = MeasurementTemplate::where('is_default', true)->get();

        return view('admin.orders.create', compact('customers', 'branches', 'products', 'templates'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $this->authorize('create', Order::class);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'required|date',
            'trial_date' => 'nullable|date',
            'delivery_date' => 'required|date|after_or_equal:order_date',
            'design_charge' => 'nullable|numeric|min:0',
            'embroidery_charge' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'fabrics' => 'nullable|array',
            'fabrics.*.product_id' => 'nullable|exists:products,id',
            'fabrics.*.quantity' => 'nullable|numeric|min:0',
            'fabrics.*.unit_price' => 'nullable|numeric|min:0',
            'fabrics.*.is_in_house' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Calculate totals
            $itemsTotal = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $fabricsTotal = collect($validated['fabrics'] ?? [])->sum(function ($fabric) {
                return ($fabric['quantity'] ?? 0) * ($fabric['unit_price'] ?? 0);
            });

            $tailorAmount = $itemsTotal;
            $fabricsAmount = $fabricsTotal;
            $totalAmount = $tailorAmount + $fabricsAmount + ($validated['design_charge'] ?? 0) + ($validated['embroidery_charge'] ?? 0);
            $netPayable = $totalAmount - ($validated['discount_amount'] ?? 0);

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'],
                'order_date' => $validated['order_date'],
                'trial_date' => $validated['trial_date'] ?? null,
                'delivery_date' => $validated['delivery_date'],
                'design_charge' => $validated['design_charge'] ?? 0,
                'embroidery_charge' => $validated['embroidery_charge'] ?? 0,
                'fabrics_amount' => $fabricsAmount,
                'tailor_amount' => $tailorAmount,
                'total_amount' => $totalAmount,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'net_payable' => $netPayable,
                'paid_amount' => 0,
                'due_amount' => $netPayable,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => Product::find($item['product_id'])->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Create order fabrics
            if (!empty($validated['fabrics'])) {
                foreach ($validated['fabrics'] as $fabric) {
                    if (!empty($fabric['product_id'])) {
                        $order->fabrics()->create([
                            'product_id' => $fabric['product_id'],
                            'fabric_name' => Product::find($fabric['product_id'])->name,
                            'quantity' => $fabric['quantity'] ?? 0,
                            'unit_price' => $fabric['unit_price'] ?? 0,
                            'total_price' => ($fabric['quantity'] ?? 0) * ($fabric['unit_price'] ?? 0),
                            'is_in_house' => $fabric['is_in_house'] ?? false,
                        ]);
                    }
                }
            }

            DB::commit();

            // Send order created notification
            $this->sendOrderCreatedNotification($order);

            return redirect()->route('admin.orders.show', $order)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'branch', 'items.product', 'fabrics.product', 'measurements', 'cuttings.cuttingMaster', 'deliveries']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Order $order)
    {
        $this->authorize('edit', $order);

        $order->load(['items', 'fabrics']);
        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('admin.orders.edit', compact('order', 'customers', 'branches', 'products'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        // Prevent editing if cutting is done
        if ($order->cuttings()->exists()) {
            return back()->with('error', 'Cannot edit order after cutting is assigned.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'required|date',
            'trial_date' => 'nullable|date',
            'delivery_date' => 'required|date|after_or_equal:order_date',
            'design_charge' => 'nullable|numeric|min:0',
            'embroidery_charge' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Recalculate totals if items/fabrics changed
            $itemsTotal = $order->items()->sum(DB::raw('quantity * unit_price'));
            $fabricsTotal = $order->fabrics()->sum(DB::raw('quantity * unit_price'));
            $totalAmount = $itemsTotal + $fabricsTotal + ($validated['design_charge'] ?? 0) + ($validated['embroidery_charge'] ?? 0);
            $netPayable = $totalAmount - ($validated['discount_amount'] ?? 0);

            $order->update([
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'],
                'order_date' => $validated['order_date'],
                'trial_date' => $validated['trial_date'] ?? null,
                'delivery_date' => $validated['delivery_date'],
                'design_charge' => $validated['design_charge'] ?? 0,
                'embroidery_charge' => $validated['embroidery_charge'] ?? 0,
                'fabrics_amount' => $fabricsTotal,
                'tailor_amount' => $itemsTotal,
                'total_amount' => $totalAmount,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'net_payable' => $netPayable,
                'due_amount' => $netPayable - $order->paid_amount,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.orders.show', $order)
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        // Prevent deletion if cutting or delivery is done
        if ($order->cuttings()->exists() || $order->deliveries()->exists()) {
            return back()->with('error', 'Cannot delete order with cutting or delivery records.');
        }

        DB::beginTransaction();
        try {
            $order->delete();
            DB::commit();

            return redirect()->route('admin.orders.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Send order created notification (SMS and Email)
     */
    protected function sendOrderCreatedNotification(Order $order): void
    {
        $order->load(['customer', 'branch']);
        $customer = $order->customer;

        if (!$customer) {
            return;
        }

        // Send SMS
        if ($customer->mobile) {
            $smsMessage = str_replace(
                ['{customer_name}', '{order_number}', '{total_amount}', '{delivery_date}'],
                [
                    $customer->name,
                    $order->order_number,
                    currency_format($order->net_payable),
                    $order->delivery_date->format('Y-m-d'),
                ],
                config('sms.templates.order_created', 'Order #{order_number} created. Total: {total_amount}. Delivery: {delivery_date}.')
            );

            SendSMS::dispatch($customer->mobile, $smsMessage);
        }

        // Send Email
        if ($customer->email) {
            SendEmail::dispatch(
                $customer->email,
                'Order Created - ' . $order->order_number,
                'emails.orders.created',
                ['order' => $order, 'customer' => $customer, 'branch' => $order->branch]
            );
        }
    }
}

