<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosExchange;
use App\Models\PosSale;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosExchangeController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:pos_exchange.view')->only(['index', 'show']);
        $this->middleware('permission:pos_exchange.create')->only(['create', 'store']);
        $this->middleware('permission:pos_exchange.edit')->only(['edit', 'update']);
        $this->middleware('permission:pos_exchange.delete')->only(['destroy']);
    }

    /**
     * Display a listing of POS exchanges
     */
    public function index(Request $request)
    {
        $this->authorize('view', PosExchange::class);

        $query = PosExchange::with(['originalSale', 'customer', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('exchange_number', 'like', "%{$search}%")
                    ->orWhereHas('originalSale', function ($q) use ($search) {
                        $q->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        $exchanges = $query->latest('exchange_date')->paginate(15);

        return view('admin.pos-exchanges.index', compact('exchanges'));
    }

    /**
     * Show the form for creating a new exchange
     */
    public function create(Request $request)
    {
        $this->authorize('create', PosExchange::class);

        $saleId = $request->input('sale_id');
        $originalSale = $saleId ? PosSale::with('items.product')->findOrFail($saleId) : null;
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('admin.pos-exchanges.create', compact('originalSale', 'branches', 'products'));
    }

    /**
     * Store a newly created exchange
     */
    public function store(Request $request)
    {
        $this->authorize('create', PosExchange::class);

        $validated = $request->validate([
            'original_sale_id' => 'required|exists:pos_sales,id',
            'customer_id' => 'nullable|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'exchange_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.original_item_id' => 'required|exists:pos_sale_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.refund_amount' => 'nullable|numeric|min:0',
            'items.*.charge_amount' => 'nullable|numeric|min:0',
            'total_refund' => 'required|numeric|min:0',
            'total_charge' => 'required|numeric|min:0',
            'net_amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $exchangeNumber = $this->generateExchangeNumber();

            $exchange = PosExchange::create([
                'exchange_number' => $exchangeNumber,
                'original_sale_id' => $validated['original_sale_id'],
                'branch_id' => $validated['branch_id'],
                'exchange_date' => $validated['exchange_date'],
                'exchange_amount' => $validated['net_amount'],
                'reason' => $validated['notes'] ?? 'Product exchange',
                'user_id' => auth()->id(),
            ]);

            // Update inventory for exchanges
            foreach ($validated['items'] as $item) {
                // Return old product to inventory
                $originalItem = \App\Models\PosSaleItem::find($item['original_item_id']);
                if ($originalItem) {
                    $inventory = Inventory::where('product_id', $originalItem->product_id)
                        ->where('branch_id', $validated['branch_id'])
                        ->where('size', $originalItem->size)
                        ->first();

                    if ($inventory) {
                        $inventory->increment('quantity', $item['quantity']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.pos-exchanges.show', $exchange)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified exchange
     */
    public function show(PosExchange $posExchange)
    {
        $this->authorize('view', $posExchange);

        $posExchange->load(['originalSale', 'customer', 'branch', 'items.product']);

        return view('admin.pos-exchanges.show', compact('posExchange'));
    }

    /**
     * Generate unique exchange number
     */
    protected function generateExchangeNumber(): string
    {
        do {
            $exchangeNumber = 'EXC-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (PosExchange::where('exchange_number', $exchangeNumber)->exists());

        return $exchangeNumber;
    }
}

