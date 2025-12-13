<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:inventory.view')->only(['index', 'show']);
        $this->middleware('permission:inventory.create')->only(['create', 'store', 'stockIn', 'stockOut']);
        $this->middleware('permission:inventory.edit')->only(['edit', 'update']);
        $this->middleware('permission:inventory.delete')->only(['destroy']);
    }

    /**
     * Display a listing of inventory
     */
    public function index(Request $request)
    {
        $this->authorize('view', Inventory::class);

        $query = Inventory::with(['product', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Low stock filter
        if ($request->filled('low_stock')) {
            $query->whereHas('product', function ($q) {
                $q->whereColumn('inventories.quantity', '<=', 'products.low_stock_alert');
            });
        }

        $inventories = $query->latest()->paginate(15);
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('admin.inventory.index', compact('inventories', 'branches', 'products'));
    }

    /**
     * Show stock in form
     */
    public function stockIn()
    {
        $this->authorize('create', Inventory::class);

        $products = Product::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.inventory.stock-in', compact('products', 'branches'));
    }

    /**
     * Process stock in
     */
    public function processStockIn(Request $request)
    {
        $this->authorize('create', Inventory::class);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'size' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get or create inventory record
            $inventory = Inventory::firstOrCreate(
                [
                    'product_id' => $validated['product_id'],
                    'branch_id' => $validated['branch_id'],
                    'size' => $validated['size'] ?? null,
                    'color' => $validated['color'] ?? null,
                ],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            // Update quantity
            $inventory->increment('quantity', $validated['quantity']);

            // Create transaction record
            InventoryTransaction::create([
                'product_id' => $validated['product_id'],
                'branch_id' => $validated['branch_id'],
                'type' => 'in',
                'reference_type' => $validated['reference_type'] ?? 'manual',
                'reference_id' => $validated['reference_id'],
                'size' => $validated['size'] ?? null,
                'color' => $validated['color'] ?? null,
                'quantity' => $validated['quantity'],
                'notes' => $validated['notes'],
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('admin.inventory.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Show stock out form
     */
    public function stockOut()
    {
        $this->authorize('create', Inventory::class);

        $products = Product::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.inventory.stock-out', compact('products', 'branches'));
    }

    /**
     * Process stock out
     */
    public function processStockOut(Request $request)
    {
        $this->authorize('create', Inventory::class);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'size' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get inventory record
            $inventory = Inventory::where('product_id', $validated['product_id'])
                ->where('branch_id', $validated['branch_id'])
                ->where('size', $validated['size'] ?? null)
                ->where('color', $validated['color'] ?? null)
                ->first();

            if (!$inventory || $inventory->available_quantity < $validated['quantity']) {
                return back()->with('error', 'Insufficient stock available.');
            }

            // Update quantity
            $inventory->decrement('quantity', $validated['quantity']);

            // Create transaction record
            InventoryTransaction::create([
                'product_id' => $validated['product_id'],
                'branch_id' => $validated['branch_id'],
                'type' => 'out',
                'reference_type' => $validated['reference_type'] ?? 'manual',
                'reference_id' => $validated['reference_id'],
                'size' => $validated['size'] ?? null,
                'color' => $validated['color'] ?? null,
                'quantity' => $validated['quantity'],
                'notes' => $validated['notes'],
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('admin.inventory.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified inventory
     */
    public function show(Inventory $inventory)
    {
        $this->authorize('view', $inventory);

        $inventory->load(['product', 'branch', 'transactions.user']);

        return view('admin.inventory.show', compact('inventory'));
    }
}

