<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Models\Branch;
use App\Enums\ExpenseType;
use App\Rules\EnumRule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpenseController extends Controller
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
     * Display a listing of expenses
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $query = Expense::with(['account', 'branch', 'user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by expense type
        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $expenses = $query->latest('expense_date')->paginate(15);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.expenses.index', compact('expenses', 'branches'));
    }

    /**
     * Show the form for creating a new expense
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('accounting.create'), 403);

        $accounts = ChartOfAccount::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.expenses.create', compact('accounts', 'branches'));
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.create'), 403);

        $validated = $request->validate([
            'expense_number' => 'nullable|string|max:50|unique:expenses,expense_number',
            'expense_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'branch_id' => 'required|exists:branches,id',
            'expense_type' => ['required', new EnumRule(ExpenseType::class, 'Expense Type')],
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'receipt_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate expense number if not provided
            if (empty($validated['expense_number'])) {
                do {
                    $expenseNumber = 'EXP' . date('Ymd') . strtoupper(Str::random(4));
                } while (Expense::where('expense_number', $expenseNumber)->exists());
                $validated['expense_number'] = $expenseNumber;
            }

            $validated['user_id'] = auth()->id();
            $expense = Expense::create($validated);

            // Create ledger entry
            $expense->account->ledgerEntries()->create([
                'transaction_date' => $expense->expense_date,
                'reference_type' => Expense::class,
                'reference_id' => $expense->id,
                'description' => $expense->description,
                'debit' => $expense->amount,
                'credit' => 0,
                'balance' => ($expense->account->ledgerEntries()->latest('transaction_date')->first()->balance ?? $expense->account->opening_balance) + $expense->amount,
                'branch_id' => $expense->branch_id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('admin.expenses.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified expense
     */
    public function show(Expense $expense)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $expense->load(['account', 'branch', 'user']);

        return view('admin.expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense
     */
    public function edit(Expense $expense)
    {
        abort_unless(auth()->user()?->can('accounting.edit'), 403);

        $accounts = ChartOfAccount::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.expenses.edit', compact('expense', 'accounts', 'branches'));
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, Expense $expense)
    {
        abort_unless(auth()->user()?->can('accounting.edit'), 403);

        $validated = $request->validate([
            'expense_number' => 'required|string|max:50|unique:expenses,expense_number,' . $expense->id,
            'expense_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'branch_id' => 'required|exists:branches,id',
            'expense_type' => ['required', new EnumRule(ExpenseType::class, 'Expense Type')],
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'receipt_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expense->update($validated);

            DB::commit();

            return redirect()->route('admin.expenses.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified expense
     */
    public function destroy(Expense $expense)
    {
        abort_unless(auth()->user()?->can('accounting.delete'), 403);

        DB::beginTransaction();
        try {
            $expense->delete();
            DB::commit();

            return redirect()->route('admin.expenses.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
