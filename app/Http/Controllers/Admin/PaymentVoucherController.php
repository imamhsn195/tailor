<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentVoucher;
use App\Models\ChartOfAccount;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentVoucherController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show']);
        $this->middleware('permission:accounting.create')->only(['create', 'store']);
        $this->middleware('permission:accounting.edit')->only(['edit', 'update']);
        $this->middleware('permission:accounting.delete')->only(['destroy']);
    }

    /**
     * Display a listing of payment vouchers
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $query = PaymentVoucher::with(['account', 'branch', 'user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                    ->orWhere('payee_name', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('voucher_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('voucher_date', '<=', $request->date_to);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $vouchers = $query->latest('voucher_date')->paginate(15);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.payment-vouchers.index', compact('vouchers', 'branches'));
    }

    /**
     * Show the form for creating a new payment voucher
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('accounting.create'), 403);

        $accounts = ChartOfAccount::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.payment-vouchers.create', compact('accounts', 'branches'));
    }

    /**
     * Store a newly created payment voucher
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.create'), 403);

        $validated = $request->validate([
            'voucher_number' => 'nullable|string|max:50|unique:payment_vouchers,voucher_number',
            'voucher_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'payee_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'cheque_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'branch_id' => 'required|exists:branches,id',
        ]);

        DB::beginTransaction();
        try {
            // Generate voucher number if not provided
            if (empty($validated['voucher_number'])) {
                do {
                    $voucherNumber = 'PV' . date('Ymd') . strtoupper(Str::random(4));
                } while (PaymentVoucher::where('voucher_number', $voucherNumber)->exists());
                $validated['voucher_number'] = $voucherNumber;
            }

            $validated['user_id'] = auth()->id();
            $voucher = PaymentVoucher::create($validated);

            // Create ledger entry
            $voucher->account->ledgerEntries()->create([
                'transaction_date' => $voucher->voucher_date,
                'reference_type' => PaymentVoucher::class,
                'reference_id' => $voucher->id,
                'description' => $voucher->description ?? 'Payment Voucher: ' . $voucher->voucher_number,
                'debit' => $voucher->amount,
                'credit' => 0,
                'balance' => ($voucher->account->ledgerEntries()->latest('transaction_date')->first()->balance ?? $voucher->account->opening_balance) + $voucher->amount,
                'branch_id' => $voucher->branch_id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('admin.payment-vouchers.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified payment voucher
     */
    public function show(PaymentVoucher $paymentVoucher)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $paymentVoucher->load(['account', 'branch', 'user']);

        return view('admin.payment-vouchers.show', compact('paymentVoucher'));
    }

    /**
     * Show the form for editing the specified payment voucher
     */
    public function edit(PaymentVoucher $paymentVoucher)
    {
        abort_unless(auth()->user()?->can('accounting.edit'), 403);

        $accounts = ChartOfAccount::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.payment-vouchers.edit', compact('paymentVoucher', 'accounts', 'branches'));
    }

    /**
     * Update the specified payment voucher
     */
    public function update(Request $request, PaymentVoucher $paymentVoucher)
    {
        abort_unless(auth()->user()?->can('accounting.edit'), 403);

        $validated = $request->validate([
            'voucher_number' => 'required|string|max:50|unique:payment_vouchers,voucher_number,' . $paymentVoucher->id,
            'voucher_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'payee_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'cheque_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'branch_id' => 'required|exists:branches,id',
        ]);

        DB::beginTransaction();
        try {
            $paymentVoucher->update($validated);

            DB::commit();

            return redirect()->route('admin.payment-vouchers.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified payment voucher
     */
    public function destroy(PaymentVoucher $paymentVoucher)
    {
        abort_unless(auth()->user()?->can('accounting.delete'), 403);

        DB::beginTransaction();
        try {
            $paymentVoucher->delete();
            DB::commit();

            return redirect()->route('admin.payment-vouchers.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
