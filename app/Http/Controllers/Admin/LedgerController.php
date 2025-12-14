<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ledger;
use App\Models\ChartOfAccount;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LedgerController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show']);
    }

    /**
     * Display a listing of ledger entries
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $query = Ledger::with(['account', 'branch', 'user']);

        // Filter by account
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $ledgers = $query->latest('transaction_date')->paginate(15);
        $accounts = ChartOfAccount::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.ledgers.index', compact('ledgers', 'accounts', 'branches'));
    }

    /**
     * Display the specified ledger entry
     */
    public function show(Ledger $ledger)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $ledger->load(['account', 'branch', 'user', 'reference']);

        return view('admin.ledgers.show', compact('ledger'));
    }
}
