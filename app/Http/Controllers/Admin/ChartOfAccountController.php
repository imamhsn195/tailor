<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Enums\AccountType;
use App\Rules\EnumRule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChartOfAccountController extends Controller
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
     * Display a listing of chart of accounts
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $query = ChartOfAccount::with('parent');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                    ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $accounts = $query->latest()->paginate(15);
        $parentAccounts = ChartOfAccount::whereNull('parent_id')->where('is_active', true)->get();

        return view('admin.chart-of-accounts.index', compact('accounts', 'parentAccounts'));
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('accounting.create'), 403);

        $parentAccounts = ChartOfAccount::whereNull('parent_id')->where('is_active', true)->get();

        return view('admin.chart-of-accounts.create', compact('parentAccounts'));
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.create'), 403);

        $validated = $request->validate([
            'account_code' => 'nullable|string|max:50|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => ['required', new EnumRule(AccountType::class, 'Account Type')],
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Generate account code if not provided
            if (empty($validated['account_code'])) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (ChartOfAccount::where('account_code', $code)->exists());
                $validated['account_code'] = $code;
            }

            ChartOfAccount::create($validated);

            DB::commit();

            return redirect()->route('admin.chart-of-accounts.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified account
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $chartOfAccount->load(['parent', 'children', 'ledgerEntries']);

        return view('admin.chart-of-accounts.show', compact('chartOfAccount'));
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        abort_unless(auth()->user()?->can('accounting.edit'), 403);

        $parentAccounts = ChartOfAccount::whereNull('parent_id')
            ->where('id', '!=', $chartOfAccount->id)
            ->where('is_active', true)
            ->get();

        return view('admin.chart-of-accounts.edit', compact('chartOfAccount', 'parentAccounts'));
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        abort_unless(auth()->user()?->can('accounting.edit'), 403);

        $validated = $request->validate([
            'account_code' => 'required|string|max:50|unique:chart_of_accounts,account_code,' . $chartOfAccount->id,
            'account_name' => 'required|string|max:255',
            'account_type' => ['required', new EnumRule(AccountType::class, 'Account Type')],
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $chartOfAccount->update($validated);

            DB::commit();

            return redirect()->route('admin.chart-of-accounts.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified account
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        abort_unless(auth()->user()?->can('accounting.delete'), 403);

        DB::beginTransaction();
        try {
            $chartOfAccount->delete();
            DB::commit();

            return redirect()->route('admin.chart-of-accounts.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
