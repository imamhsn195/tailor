<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Customer;
use App\Models\Membership;
use App\Models\Product;
use App\Models\Branch;
use App\Enums\DiscountType;
use App\Rules\EnumRule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:customer.view')->only(['index', 'show']);
        $this->middleware('permission:customer.create')->only(['create', 'store']);
        $this->middleware('permission:customer.edit')->only(['edit', 'update']);
        $this->middleware('permission:customer.delete')->only(['destroy']);
    }

    /**
     * Display a listing of discounts
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $query = Discount::with(['customer', 'membership', 'product', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $discounts = $query->latest()->paginate(15);

        return view('admin.discounts.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new discount
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        $customers = Customer::where('is_active', true)->get();
        $memberships = Membership::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.discounts.create', compact('customers', 'memberships', 'products', 'branches'));
    }

    /**
     * Store a newly created discount
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', new EnumRule(DiscountType::class, 'Type')],
            'value' => 'required|numeric|min:0',
            'applicable_to' => 'nullable|in:customer_id,company,membership,product',
            'customer_id' => 'nullable|exists:customers,id',
            'membership_id' => 'nullable|exists:memberships,id',
            'product_id' => 'nullable|exists:products,id',
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            Discount::create($validated);

            DB::commit();

            return redirect()->route('admin.discounts.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified discount
     */
    public function show(Discount $discount)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $discount->load(['customer', 'membership', 'product', 'branch']);

        return view('admin.discounts.show', compact('discount'));
    }

    /**
     * Show the form for editing the specified discount
     */
    public function edit(Discount $discount)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $customers = Customer::where('is_active', true)->get();
        $memberships = Membership::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.discounts.edit', compact('discount', 'customers', 'memberships', 'products', 'branches'));
    }

    /**
     * Update the specified discount
     */
    public function update(Request $request, Discount $discount)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', new EnumRule(DiscountType::class, 'Type')],
            'value' => 'required|numeric|min:0',
            'applicable_to' => 'nullable|in:customer_id,company,membership,product',
            'customer_id' => 'nullable|exists:customers,id',
            'membership_id' => 'nullable|exists:memberships,id',
            'product_id' => 'nullable|exists:products,id',
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $discount->update($validated);

            DB::commit();

            return redirect()->route('admin.discounts.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified discount
     */
    public function destroy(Discount $discount)
    {
        abort_unless(auth()->user()?->can('customer.delete'), 403);

        DB::beginTransaction();
        try {
            $discount->delete();
            DB::commit();

            return redirect()->route('admin.discounts.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
