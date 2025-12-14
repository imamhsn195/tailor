<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosSaleController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:pos_sale.view')->only(['index', 'show']);
        $this->middleware('permission:pos_sale.create')->only(['create', 'store']);
        $this->middleware('permission:pos_sale.edit')->only(['edit', 'update']);
        $this->middleware('permission:pos_sale.delete')->only(['destroy']);
    }

    /**
     * Display POS interface
     */
    public function create()
    {
        $this->authorize('create', PosSale::class);

        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with('category', 'unit')->get();

        return view('admin.pos.create', compact('customers', 'branches', 'products'));
    }

    /**
     * Process POS sale
     */
    public function store(Request $request)
    {
        $this->authorize('create', PosSale::class);

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.barcode' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'vat_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Create POS sale
            $posSale = PosSale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'branch_id' => $validated['branch_id'],
                'seller_id' => auth()->id(),
                'sale_date' => $validated['sale_date'],
                'subtotal' => $validated['subtotal'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'vat_amount' => $validated['vat_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'sender_mobile' => $request->input('sender_mobile'),
                'account_number' => $request->input('account_number'),
                'card_last_4' => $request->input('card_last_4'),
                'customer_name' => $request->input('customer_name'),
                'customer_mobile' => $request->input('customer_mobile'),
            ]);

            // Create sale items and update inventory
            foreach ($validated['items'] as $item) {
                $posSale->items()->create([
                    'product_id' => $item['product_id'],
                    'barcode' => $item['barcode'] ?? null,
                    'product_name' => Product::find($item['product_id'])->name,
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total_price' => ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0),
                ]);

                // Update inventory (stock out)
                $inventory = Inventory::where('product_id', $item['product_id'])
                    ->where('branch_id', $validated['branch_id'])
                    ->where('size', $item['size'] ?? null)
                    ->first();

                if ($inventory && $inventory->available_quantity >= $item['quantity']) {
                    $inventory->decrement('quantity', $item['quantity']);

                    // Create inventory transaction
                    \App\Models\InventoryTransaction::create([
                        'product_id' => $item['product_id'],
                        'branch_id' => $validated['branch_id'],
                        'type' => 'out',
                        'reference_type' => 'pos_sale',
                        'reference_id' => $posSale->id,
                        'size' => $item['size'] ?? null,
                        'quantity' => $item['quantity'],
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.pos-sales.show', $posSale)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of POS sales
     */
    public function index(Request $request)
    {
        $this->authorize('view', PosSale::class);

        $query = PosSale::with(['customer', 'branch', 'seller']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%")
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

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->latest('sale_date')->paginate(15);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.pos-sales.index', compact('sales', 'branches'));
    }

    /**
     * Display the specified POS sale
     */
    public function show(PosSale $posSale)
    {
        $this->authorize('view', $posSale);

        $posSale->load(['customer', 'branch', 'seller', 'items.product']);

        return view('admin.pos-sales.show', compact('posSale'));
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        do {
            $invoiceNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (PosSale::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }
}

