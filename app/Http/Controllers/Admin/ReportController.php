<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosSale;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Employee;
use App\Models\ChartOfAccount;
use App\Models\Ledger;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:report.view')->only(['index', 'orders', 'sales', 'inventory', 'factory', 'hr', 'accounting']);
    }

    /**
     * Display the reports index page
     */
    public function index()
    {
        abort_unless(auth()->user()?->can('report.view'), 403);

        return view('admin.reports.index');
    }

    /**
     * Display order reports
     */
    public function orders(Request $request)
    {
        abort_unless(auth()->user()?->can('report.view'), 403);

        $query = Order::with(['customer', 'branch']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest('order_date')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        // Summary statistics
        $summary = [
            'total_orders' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];

        return view('admin.reports.orders', compact('orders', 'branches', 'summary'));
    }

    /**
     * Display sales reports (POS Sales)
     */
    public function sales(Request $request)
    {
        abort_unless(auth()->user()?->can('report.view'), 403);

        $query = PosSale::with(['customer', 'branch']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest('sale_date')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        // Summary statistics
        $summary = [
            'total_sales' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'total_vat' => $query->sum('vat_amount'),
            'total_discount' => $query->sum('discount_amount'),
            'cash_sales' => (clone $query)->where('payment_method', 'cash')->sum('total_amount'),
        ];

        return view('admin.reports.sales', compact('sales', 'branches', 'summary'));
    }

    /**
     * Display inventory reports
     */
    public function inventory(Request $request)
    {
        abort_unless(auth()->user()?->can('report.view'), 403);

        $query = Inventory::with(['product', 'branch']);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter low stock
        if ($request->filled('low_stock')) {
            $query->whereHas('product', function ($q) {
                $q->whereColumn('inventories.quantity', '<=', 'products.low_stock_alert');
            });
        }

        $inventories = $query->latest()->paginate(20);
        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        // Summary statistics
        $summary = [
            'total_items' => $query->count(),
            'total_quantity' => $query->sum('quantity'),
            'total_value' => $query->join('products', 'inventories.product_id', '=', 'products.id')
                ->sum(DB::raw('inventories.quantity * products.purchase_price')),
            'low_stock_items' => $query->whereHas('product', function ($q) {
                $q->whereColumn('inventories.quantity', '<=', 'products.low_stock_alert');
            })->count(),
        ];

        return view('admin.reports.inventory', compact('inventories', 'branches', 'products', 'summary'));
    }

    /**
     * Display factory production reports
     */
    public function factory(Request $request)
    {
        abort_unless(auth()->user()?->can('report.view'), 403);

        // This will need to be implemented when Factory models are available
        // For now, return a placeholder view
        
        $query = DB::table('factory_productions')
            ->select('factory_productions.*')
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->where('production_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->where('production_date', '<=', $request->date_to);
            });

        $productions = $query->latest('production_date')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        $summary = [
            'total_productions' => $query->count(),
        ];

        return view('admin.reports.factory', compact('productions', 'branches', 'summary'));
    }

    /**
     * Display HR reports
     */
    public function hr(Request $request)
    {
        abort_unless(auth()->user()?->can('report.view'), 403);

        $query = Employee::with(['department', 'designation', 'branch']);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by designation
        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        $employees = $query->latest()->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        // Summary statistics
        $summary = [
            'total_employees' => $query->count(),
            'active_employees' => (clone $query)->where('is_active', true)->count(),
            'total_salary' => DB::table('salaries')
                ->join('employees', 'salaries.employee_id', '=', 'employees.id')
                ->when($request->filled('branch_id'), function ($q) use ($request) {
                    $q->where('employees.branch_id', $request->branch_id);
                })
                ->sum('salaries.gross_salary'),
        ];

        return view('admin.reports.hr', compact('employees', 'branches', 'summary'));
    }

    /**
     * Display accounting reports
     */
    public function accounting(Request $request)
    {
        abort_unless(auth()->user()?->can('report.view'), 403);

        $query = Ledger::with(['account', 'branch']);

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

        $ledgers = $query->latest('transaction_date')->paginate(20);
        $accounts = ChartOfAccount::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        // Summary statistics
        $summary = [
            'total_transactions' => $query->count(),
            'total_debit' => $query->sum('debit'),
            'total_credit' => $query->sum('credit'),
            'balance' => $query->sum(DB::raw('debit - credit')),
        ];

        return view('admin.reports.accounting', compact('ledgers', 'accounts', 'branches', 'summary'));
    }
}
