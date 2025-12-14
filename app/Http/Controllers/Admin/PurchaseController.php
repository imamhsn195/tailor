<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:purchase.view')->only(['index', 'show']);
        $this->middleware('permission:purchase.create')->only(['create', 'store']);
        $this->middleware('permission:purchase.edit')->only(['edit', 'update']);
        $this->middleware('permission:purchase.delete')->only(['destroy']);
    }

    /**
     * Display a listing of purchases
     */
    public function index(Request $request)
    {
        $this->authorize('view', Purchase::class);

        $query = Purchase::with(['supplier', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('purchase_date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->latest('purchase_date')->paginate(15);
        $suppliers = Supplier::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers', 'branches'));
    }

    /**
     * Show the form for creating a new purchase
     */
    public function create()
    {
        $this->authorize('create', Purchase::class);

        $suppliers = Supplier::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('admin.purchases.create', compact('suppliers', 'branches', 'products'));
    }

    /**
     * Store a newly created purchase
     */
    public function store(Request $request)
    {
        $this->authorize('create', Purchase::class);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'purchase_date' => 'required|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.barcode' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate purchase number
            $purchaseNumber = $this->generatePurchaseNumber();

            // Calculate totals
            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $totalAmount = $subtotal - ($validated['discount_amount'] ?? 0) + ($validated['vat_amount'] ?? 0);

            // Create purchase
            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $validated['branch_id'],
                'purchase_date' => $validated['purchase_date'],
                'subtotal' => $subtotal,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'vat_amount' => $validated['vat_amount'] ?? 0,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'due_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Create purchase items and update inventory
            foreach ($validated['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'barcode' => $item['barcode'] ?? null,
                    'product_name' => Product::find($item['product_id'])->name,
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);

                // Update inventory (stock in)
                $inventory = Inventory::firstOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'branch_id' => $validated['branch_id'],
                        'size' => $item['size'] ?? null,
                        'color' => null,
                    ],
                    ['quantity' => 0, 'reserved_quantity' => 0]
                );

                $inventory->increment('quantity', $item['quantity']);

                // Create inventory transaction
                \App\Models\InventoryTransaction::create([
                    'product_id' => $item['product_id'],
                    'branch_id' => $validated['branch_id'],
                    'type' => 'in',
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'user_id' => auth()->id(),
                ]);
            }

            // Update supplier totals
            $supplier = Supplier::find($validated['supplier_id']);
            $supplier->increment('total_purchase_amount', $totalAmount);
            $supplier->increment('total_due_amount', $totalAmount);

            DB::commit();

            return redirect()->route('admin.purchases.show', $purchase)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase
     */
    public function show(Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        $purchase->load(['supplier', 'branch', 'items.product', 'user']);

        return view('admin.purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase
     */
    public function edit(Purchase $purchase)
    {
        $this->authorize('edit', $purchase);

        // Prevent editing if payment is made
        if ($purchase->paid_amount > 0) {
            return back()->with('error', 'Cannot edit purchase with payments made.');
        }

        $purchase->load(['items']);
        $suppliers = Supplier::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'branches', 'products'));
    }

    /**
     * Update the specified purchase
     */
    public function update(Request $request, Purchase $purchase)
    {
        $this->authorize('update', $purchase);

        // Prevent editing if payment is made
        if ($purchase->paid_amount > 0) {
            return back()->with('error', 'Cannot edit purchase with payments made.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'purchase_date' => 'required|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Recalculate totals if items changed
            $subtotal = $purchase->items()->sum(DB::raw('quantity * unit_price'));
            $totalAmount = $subtotal - ($validated['discount_amount'] ?? 0) + ($validated['vat_amount'] ?? 0);

            // Update supplier totals
            $oldAmount = $purchase->total_amount;
            $supplier = Supplier::find($validated['supplier_id']);
            $supplier->decrement('total_purchase_amount', $oldAmount);
            $supplier->decrement('total_due_amount', $oldAmount);
            $supplier->increment('total_purchase_amount', $totalAmount);
            $supplier->increment('total_due_amount', $totalAmount);

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $validated['branch_id'],
                'purchase_date' => $validated['purchase_date'],
                'subtotal' => $subtotal,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'vat_amount' => $validated['vat_amount'] ?? 0,
                'total_amount' => $totalAmount,
                'due_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.purchases.show', $purchase)
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified purchase
     */
    public function destroy(Purchase $purchase)
    {
        $this->authorize('delete', $purchase);

        // Prevent deletion if payment is made
        if ($purchase->paid_amount > 0) {
            return back()->with('error', 'Cannot delete purchase with payments made.');
        }

        DB::beginTransaction();
        try {
            // Update supplier totals
            $supplier = $purchase->supplier;
            $supplier->decrement('total_purchase_amount', $purchase->total_amount);
            $supplier->decrement('total_due_amount', $purchase->total_amount);

            // Reverse inventory
            foreach ($purchase->items as $item) {
                $inventory = Inventory::where('product_id', $item->product_id)
                    ->where('branch_id', $purchase->branch_id)
                    ->where('size', $item->size)
                    ->first();

                if ($inventory && $inventory->quantity >= $item->quantity) {
                    $inventory->decrement('quantity', $item->quantity);
                }
            }

            $purchase->delete();

            DB::commit();

            return redirect()->route('admin.purchases.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Generate unique purchase number
     */
    protected function generatePurchaseNumber(): string
    {
        do {
            $purchaseNumber = 'PUR-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Purchase::where('purchase_number', $purchaseNumber)->exists());

        return $purchaseNumber;
    }
}

