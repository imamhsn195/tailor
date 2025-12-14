<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupplierPayment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierPaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:supplier_payment.view')->only(['index', 'show']);
        $this->middleware('permission:supplier_payment.create')->only(['create', 'store']);
        $this->middleware('permission:supplier_payment.edit')->only(['edit', 'update']);
        $this->middleware('permission:supplier_payment.delete')->only(['destroy']);
    }

    /**
     * Display a listing of supplier payments
     */
    public function index(Request $request)
    {
        $this->authorize('view', SupplierPayment::class);

        $query = SupplierPayment::with('supplier');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest('payment_date')->paginate(15);
        $suppliers = Supplier::where('is_active', true)->get();

        return view('admin.supplier-payments.index', compact('payments', 'suppliers'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(Request $request)
    {
        $this->authorize('create', SupplierPayment::class);

        $supplierId = $request->input('supplier_id');
        $supplier = $supplierId ? Supplier::findOrFail($supplierId) : null;
        $suppliers = Supplier::where('is_active', true)->get();

        return view('admin.supplier-payments.create', compact('supplier', 'suppliers'));
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        $this->authorize('create', SupplierPayment::class);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'cheque_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment = SupplierPayment::create($validated);

            // Update supplier payment totals
            $supplier = Supplier::find($validated['supplier_id']);
            $supplier->increment('total_paid_amount', $validated['amount']);
            $supplier->decrement('total_due_amount', $validated['amount']);

            DB::commit();

            return redirect()->route('admin.supplier-payments.show', $payment)
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment
     */
    public function show(SupplierPayment $supplierPayment)
    {
        $this->authorize('view', $supplierPayment);

        $supplierPayment->load('supplier');

        return view('admin.supplier-payments.show', compact('supplierPayment'));
    }

    /**
     * Show the form for editing the specified payment
     */
    public function edit(SupplierPayment $supplierPayment)
    {
        $this->authorize('edit', $supplierPayment);

        $suppliers = Supplier::where('is_active', true)->get();

        return view('admin.supplier-payments.edit', compact('supplierPayment', 'suppliers'));
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, SupplierPayment $supplierPayment)
    {
        $this->authorize('update', $supplierPayment);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'cheque_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update supplier totals
            $oldAmount = $supplierPayment->amount;
            $oldSupplier = $supplierPayment->supplier;
            $oldSupplier->decrement('total_paid_amount', $oldAmount);
            $oldSupplier->increment('total_due_amount', $oldAmount);

            $supplierPayment->update($validated);

            $newSupplier = Supplier::find($validated['supplier_id']);
            $newSupplier->increment('total_paid_amount', $validated['amount']);
            $newSupplier->decrement('total_due_amount', $validated['amount']);

            DB::commit();

            return redirect()->route('admin.supplier-payments.show', $supplierPayment)
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(SupplierPayment $supplierPayment)
    {
        $this->authorize('delete', $supplierPayment);

        DB::beginTransaction();
        try {
            // Update supplier totals
            $supplier = $supplierPayment->supplier;
            $supplier->decrement('total_paid_amount', $supplierPayment->amount);
            $supplier->increment('total_due_amount', $supplierPayment->amount);

            $supplierPayment->delete();

            DB::commit();

            return redirect()->route('admin.supplier-payments.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}

