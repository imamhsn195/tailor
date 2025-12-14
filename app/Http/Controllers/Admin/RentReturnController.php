<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentReturn;
use App\Models\RentOrder;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class RentReturnController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:rent_return.view')->only(['index', 'show']);
        $this->middleware('permission:rent_return.create')->only(['create', 'store']);
        $this->middleware('permission:rent_return.edit')->only(['edit', 'update']);
        $this->middleware('permission:rent_return.delete')->only(['destroy']);
    }

    /**
     * Display a listing of rent returns
     */
    public function index(Request $request)
    {
        $this->authorize('view', RentReturn::class);

        $query = RentReturn::with(['rentOrder.customer', 'rentOrder.branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('rentOrder', function ($q) use ($search) {
                $q->where('rent_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('return_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('return_date', '<=', $request->date_to);
        }

        $returns = $query->latest('return_date')->paginate(15);

        return view('admin.rent-returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new return
     */
    public function create(Request $request)
    {
        $this->authorize('create', RentReturn::class);

        $rentOrderId = $request->input('rent_order_id');
        $rentOrder = $rentOrderId ? RentOrder::with(['items.product', 'items.inventory'])->findOrFail($rentOrderId) : null;
        $rentOrders = RentOrder::where('status', 'active')
            ->with('customer')
            ->latest('rent_date')
            ->get();

        return view('admin.rent-returns.create', compact('rentOrder', 'rentOrders'));
    }

    /**
     * Store a newly created return
     */
    public function store(Request $request)
    {
        $this->authorize('create', RentReturn::class);

        $validated = $request->validate([
            'rent_order_id' => 'required|exists:rent_orders,id',
            'return_date' => 'required|date',
            'return_status' => 'required|string',
            'damage_charges' => 'nullable|numeric|min:0',
            'late_fees' => 'nullable|numeric|min:0',
            'refund_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.rent_order_item_id' => 'required|exists:rent_order_items,id',
            'items.*.condition' => 'required|string',
            'items.*.damage_charges' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $rentOrder = RentOrder::findOrFail($validated['rent_order_id']);

            // Calculate late fees if overdue
            $lateFees = $validated['late_fees'] ?? 0;
            if (!$lateFees && $rentOrder->expected_return_date < $validated['return_date']) {
                $daysOverdue = now()->diffInDays($rentOrder->expected_return_date);
                // Calculate late fees (example: 100 per day)
                $lateFees = $daysOverdue * 100; // This should be configurable
            }

            // Create return
            $rentReturn = RentReturn::create([
                'rent_order_id' => $validated['rent_order_id'],
                'return_date' => $validated['return_date'],
                'return_status' => $validated['return_status'],
                'damage_charges' => $validated['damage_charges'] ?? 0,
                'late_fees' => $lateFees,
                'refund_amount' => $validated['refund_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Create return items and update inventory
            foreach ($validated['items'] as $item) {
                $rentOrderItem = \App\Models\RentOrderItem::find($item['rent_order_item_id']);
                
                $returnItem = $rentReturn->items()->create([
                    'rent_order_item_id' => $item['rent_order_item_id'],
                    'product_id' => $rentOrderItem->product_id,
                    'inventory_id' => $rentOrderItem->inventory_id,
                    'condition' => $item['condition'],
                    'damage_charges' => $item['damage_charges'] ?? 0,
                ]);

                // Return inventory if condition is good or damaged (not lost)
                if ($item['condition'] !== 'lost' && $rentOrderItem->inventory_id) {
                    $inventory = Inventory::find($rentOrderItem->inventory_id);
                    if ($inventory) {
                        // Release reserved quantity
                        if ($inventory->reserved_quantity >= 1) {
                            $inventory->decrement('reserved_quantity', 1);
                        }
                        
                        // Add back to quantity if condition is good
                        if ($item['condition'] === 'good') {
                            $inventory->increment('quantity', 1);
                            
                            // Create inventory transaction
                            \App\Models\InventoryTransaction::create([
                                'product_id' => $rentOrderItem->product_id,
                                'branch_id' => $rentOrder->branch_id,
                                'type' => 'in',
                                'reference_type' => 'rent_return',
                                'reference_id' => $rentReturn->id,
                                'size' => $rentOrderItem->size,
                                'quantity' => 1,
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            // Update rent order status
            $rentOrder->update([
                'status' => 'returned',
                'actual_return_date' => $validated['return_date'],
            ]);

            DB::commit();

            return redirect()->route('admin.rent-returns.show', $rentReturn)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified return
     */
    public function show(RentReturn $rentReturn)
    {
        $this->authorize('view', $rentReturn);

        $rentReturn->load(['rentOrder.customer', 'rentOrder.branch', 'items.rentOrderItem.product', 'user']);

        return view('admin.rent-returns.show', compact('rentReturn'));
    }
}

