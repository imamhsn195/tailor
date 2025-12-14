<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentOrder;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RentOrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:rent_order.view')->only(['index', 'show']);
        $this->middleware('permission:rent_order.create')->only(['create', 'store']);
        $this->middleware('permission:rent_order.edit')->only(['edit', 'update']);
        $this->middleware('permission:rent_order.delete')->only(['destroy']);
    }

    /**
     * Display a listing of rent orders
     */
    public function index(Request $request)
    {
        $this->authorize('view', RentOrder::class);

        $query = RentOrder::with(['customer', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rent_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('rent_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('rent_date', '<=', $request->date_to);
        }

        // Filter overdue
        if ($request->filled('overdue')) {
            $query->where('status', 'active')
                ->where('expected_return_date', '<', now());
        }

        $rentOrders = $query->latest('rent_date')->paginate(15);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.rent-orders.index', compact('rentOrders', 'branches'));
    }

    /**
     * Show the form for creating a new rent order
     */
    public function create()
    {
        $this->authorize('create', RentOrder::class);

        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('admin.rent-orders.create', compact('customers', 'branches', 'products'));
    }

    /**
     * Store a newly created rent order
     */
    public function store(Request $request)
    {
        $this->authorize('create', RentOrder::class);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'rent_date' => 'required|date',
            'expected_return_date' => 'required|date|after:rent_date',
            'rent_amount' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.inventory_id' => 'nullable|exists:inventories,id',
            'items.*.barcode' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.rent_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate rent number
            $rentNumber = $this->generateRentNumber();

            // Create rent order
            $rentOrder = RentOrder::create([
                'rent_number' => $rentNumber,
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'],
                'rent_date' => $validated['rent_date'],
                'expected_return_date' => $validated['expected_return_date'],
                'rent_amount' => $validated['rent_amount'],
                'security_deposit' => $validated['security_deposit'],
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Create rent order items and update inventory
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                $rentOrderItem = $rentOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'inventory_id' => $item['inventory_id'] ?? null,
                    'barcode' => $item['barcode'] ?? null,
                    'product_name' => $product->name,
                    'size' => $item['size'] ?? null,
                    'rent_price' => $item['rent_price'],
                ]);

                // Update inventory (reserve quantity for rent)
                if ($item['inventory_id']) {
                    $inventory = Inventory::find($item['inventory_id']);
                    if ($inventory && $inventory->available_quantity >= 1) {
                        $inventory->increment('reserved_quantity', 1);
                        
                        // Create inventory transaction
                        \App\Models\InventoryTransaction::create([
                            'product_id' => $item['product_id'],
                            'branch_id' => $validated['branch_id'],
                            'type' => 'out',
                            'reference_type' => 'rent_order',
                            'reference_id' => $rentOrder->id,
                            'size' => $item['size'] ?? null,
                            'quantity' => 1,
                            'user_id' => auth()->id(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.rent-orders.show', $rentOrder)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified rent order
     */
    public function show(RentOrder $rentOrder)
    {
        $this->authorize('view', $rentOrder);

        $rentOrder->load(['customer', 'branch', 'items.product', 'items.inventory', 'deliveries', 'returns.items', 'user']);

        return view('admin.rent-orders.show', compact('rentOrder'));
    }

    /**
     * Show the form for editing the specified rent order
     */
    public function edit(RentOrder $rentOrder)
    {
        $this->authorize('edit', $rentOrder);

        // Prevent editing if returned
        if ($rentOrder->status === 'returned') {
            return back()->with('error', 'Cannot edit returned rent order.');
        }

        $rentOrder->load(['items']);
        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('admin.rent-orders.edit', compact('rentOrder', 'customers', 'branches', 'products'));
    }

    /**
     * Update the specified rent order
     */
    public function update(Request $request, RentOrder $rentOrder)
    {
        $this->authorize('update', $rentOrder);

        // Prevent editing if returned
        if ($rentOrder->status === 'returned') {
            return back()->with('error', 'Cannot edit returned rent order.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'rent_date' => 'required|date',
            'expected_return_date' => 'required|date|after:rent_date',
            'rent_amount' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $rentOrder->update($validated);

        return redirect()->route('admin.rent-orders.show', $rentOrder)
            ->with('success', trans_common('updated_successfully'));
    }

    /**
     * Remove the specified rent order
     */
    public function destroy(RentOrder $rentOrder)
    {
        $this->authorize('delete', $rentOrder);

        // Prevent deletion if returned
        if ($rentOrder->status === 'returned') {
            return back()->with('error', 'Cannot delete returned rent order.');
        }

        DB::beginTransaction();
        try {
            // Release reserved inventory
            foreach ($rentOrder->items as $item) {
                if ($item->inventory_id) {
                    $inventory = Inventory::find($item->inventory_id);
                    if ($inventory && $inventory->reserved_quantity >= 1) {
                        $inventory->decrement('reserved_quantity', 1);
                    }
                }
            }

            $rentOrder->delete();

            DB::commit();

            return redirect()->route('admin.rent-orders.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Generate unique rent number
     */
    protected function generateRentNumber(): string
    {
        do {
            $rentNumber = 'RENT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (RentOrder::where('rent_number', $rentNumber)->exists());

        return $rentNumber;
    }
}

