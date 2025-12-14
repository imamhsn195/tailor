<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftVoucher;
use App\Models\Customer;
use App\Enums\GiftVoucherStatus;
use App\Rules\EnumRule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GiftVoucherController extends Controller
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
     * Display a listing of gift vouchers
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $query = GiftVoucher::with('customer');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $giftVouchers = $query->latest()->paginate(15);
        $customers = Customer::where('is_active', true)->get();

        return view('admin.gift-vouchers.index', compact('giftVouchers', 'customers'));
    }

    /**
     * Show the form for creating a new gift voucher
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        $customers = Customer::where('is_active', true)->get();

        return view('admin.gift-vouchers.create', compact('customers'));
    }

    /**
     * Store a newly created gift voucher
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        $validated = $request->validate([
            'voucher_code' => 'nullable|string|max:50|unique:gift_vouchers,voucher_code',
            'name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'issued_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:issued_date',
            'status' => ['required', new EnumRule(GiftVoucherStatus::class, 'Status')],
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate voucher code if not provided
            if (empty($validated['voucher_code'])) {
                do {
                    $code = 'GV' . strtoupper(Str::random(10));
                } while (GiftVoucher::where('voucher_code', $code)->exists());
                $validated['voucher_code'] = $code;
            }

            GiftVoucher::create($validated);

            DB::commit();

            return redirect()->route('admin.gift-vouchers.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified gift voucher
     */
    public function show(GiftVoucher $giftVoucher)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $giftVoucher->load('customer');

        return view('admin.gift-vouchers.show', compact('giftVoucher'));
    }

    /**
     * Show the form for editing the specified gift voucher
     */
    public function edit(GiftVoucher $giftVoucher)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $customers = Customer::where('is_active', true)->get();

        return view('admin.gift-vouchers.edit', compact('giftVoucher', 'customers'));
    }

    /**
     * Update the specified gift voucher
     */
    public function update(Request $request, GiftVoucher $giftVoucher)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $validated = $request->validate([
            'voucher_code' => 'required|string|max:50|unique:gift_vouchers,voucher_code,' . $giftVoucher->id,
            'name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'issued_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:issued_date',
            'status' => ['required', new EnumRule(GiftVoucherStatus::class, 'Status')],
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $giftVoucher->update($validated);

            DB::commit();

            return redirect()->route('admin.gift-vouchers.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified gift voucher
     */
    public function destroy(GiftVoucher $giftVoucher)
    {
        abort_unless(auth()->user()?->can('customer.delete'), 403);

        DB::beginTransaction();
        try {
            $giftVoucher->delete();
            DB::commit();

            return redirect()->route('admin.gift-vouchers.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
