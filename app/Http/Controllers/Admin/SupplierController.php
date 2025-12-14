<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:supplier.view')->only(['index', 'show', 'ledger']);
        $this->middleware('permission:supplier.create')->only(['create', 'store']);
        $this->middleware('permission:supplier.edit')->only(['edit', 'update']);
        $this->middleware('permission:supplier.delete')->only(['destroy']);
    }

    /**
     * Display a listing of suppliers
     */
    public function index(Request $request)
    {
        $this->authorize('view', Supplier::class);

        $query = Supplier::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $suppliers = $query->latest()->paginate(15);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create()
    {
        $this->authorize('create', Supplier::class);

        return view('admin.suppliers.create');
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $this->authorize('create', Supplier::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'vat_no' => 'nullable|string|max:255',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', trans_common('created_successfully'));
    }

    /**
     * Display the specified supplier
     */
    public function show(Supplier $supplier)
    {
        $this->authorize('view', $supplier);

        $supplier->load(['purchases.branch', 'payments']);

        return view('admin.suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit(Supplier $supplier)
    {
        $this->authorize('edit', $supplier);

        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'vat_no' => 'nullable|string|max:255',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('admin.suppliers.show', $supplier)
            ->with('success', trans_common('updated_successfully'));
    }

    /**
     * Remove the specified supplier
     */
    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', trans_common('deleted_successfully'));
    }

    /**
     * Display supplier account ledger
     */
    public function ledger(Supplier $supplier, Request $request)
    {
        $this->authorize('view', $supplier);

        $dateFrom = $request->get('date_from', now()->startOfYear()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $purchases = $supplier->purchases()
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->with('branch')
            ->latest('purchase_date')
            ->get();

        $payments = $supplier->payments()
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->latest('payment_date')
            ->get();

        // Calculate opening balance (purchases before date_from - payments before date_from)
        $openingPurchases = $supplier->purchases()
            ->where('purchase_date', '<', $dateFrom)
            ->sum('total_amount');

        $openingPayments = $supplier->payments()
            ->where('payment_date', '<', $dateFrom)
            ->sum('amount');

        $openingBalance = $openingPurchases - $openingPayments;

        return view('admin.suppliers.ledger', compact('supplier', 'purchases', 'payments', 'openingBalance', 'dateFrom', 'dateTo'));
    }
}

