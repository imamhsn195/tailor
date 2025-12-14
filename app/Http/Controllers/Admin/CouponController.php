<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Enums\CouponType;
use App\Rules\EnumRule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponController extends Controller
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
     * Display a listing of coupons
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $query = Coupon::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
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

        $coupons = $query->latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new coupon
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        return view('admin.coupons.create');
    }

    /**
     * Store a newly created coupon
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'type' => ['required', new EnumRule(CouponType::class, 'Type')],
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Generate code if not provided
            if (empty($validated['code'])) {
                do {
                    $code = strtoupper(Str::random(10));
                } while (Coupon::where('code', $code)->exists());
                $validated['code'] = $code;
            }

            Coupon::create($validated);

            DB::commit();

            return redirect()->route('admin.coupons.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified coupon
     */
    public function show(Coupon $coupon)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        return view('admin.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified coupon
     */
    public function edit(Coupon $coupon)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'required|string|max:255',
            'type' => ['required', new EnumRule(CouponType::class, 'Type')],
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $coupon->update($validated);

            DB::commit();

            return redirect()->route('admin.coupons.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified coupon
     */
    public function destroy(Coupon $coupon)
    {
        abort_unless(auth()->user()?->can('customer.delete'), 403);

        DB::beginTransaction();
        try {
            $coupon->delete();
            DB::commit();

            return redirect()->route('admin.coupons.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
