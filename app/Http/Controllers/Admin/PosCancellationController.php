<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosCancellation;
use App\Models\PosSale;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosCancellationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:pos_cancellation.view')->only(['index', 'show']);
        $this->middleware('permission:pos_cancellation.create')->only(['create', 'store']);
        $this->middleware('permission:pos_cancellation.edit')->only(['edit', 'update']);
        $this->middleware('permission:pos_cancellation.delete')->only(['destroy']);
    }

    /**
     * Display a listing of POS cancellations
     */
    public function index(Request $request)
    {
        $this->authorize('view', PosCancellation::class);

        $query = PosCancellation::with(['originalSale', 'customer', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('originalSale', function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%");
                });
            });
        }

        $cancellations = $query->latest('cancellation_date')->paginate(15);

        return view('admin.pos-cancellations.index', compact('cancellations'));
    }

    /**
     * Show the form for creating a new cancellation
     */
    public function create(Request $request)
    {
        $this->authorize('create', PosCancellation::class);

        $saleId = $request->input('sale_id');
        $originalSale = $saleId ? PosSale::with('items.product')->findOrFail($saleId) : null;

        return view('admin.pos-cancellations.create', compact('originalSale'));
    }

    /**
     * Store a newly created cancellation
     */
    public function store(Request $request)
    {
        $this->authorize('create', PosCancellation::class);

        $validated = $request->validate([
            'pos_sale_id' => 'required|exists:pos_sales,id',
            'cancellation_date' => 'required|date',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $cancellation = PosCancellation::create([
                'pos_sale_id' => $validated['pos_sale_id'],
                'cancellation_date' => $validated['cancellation_date'],
                'reason' => $validated['reason'],
                'user_id' => auth()->id(),
            ]);

            // Get original sale and return items to inventory
            $originalSale = PosSale::with('items')->findOrFail($validated['pos_sale_id']);
            
            foreach ($originalSale->items as $item) {
                // Return items to inventory
                $inventory = Inventory::where('product_id', $item->product_id)
                    ->where('branch_id', $originalSale->branch_id)
                    ->where('size', $item->size)
                    ->first();

                if ($inventory) {
                    $inventory->increment('quantity', $item->quantity);

                    // Create inventory transaction
                    \App\Models\InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'branch_id' => $originalSale->branch_id,
                        'type' => 'in',
                        'reference_type' => 'pos_cancellation',
                        'reference_id' => $cancellation->id,
                        'size' => $item->size,
                        'quantity' => $item->quantity,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.pos-cancellations.show', $cancellation)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified cancellation
     */
    public function show(PosCancellation $posCancellation)
    {
        $this->authorize('view', $posCancellation);

        $posCancellation->load(['originalSale.items.product', 'user']);

        return view('admin.pos-cancellations.show', compact('posCancellation'));
    }

}

