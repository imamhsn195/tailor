<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosSale;
use App\Models\Inventory;
use App\Models\FactoryProduction;
use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\ChartOfAccount;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:report.view')->only(['index', 'show', 'export']);
    }

    /**
     * Display reports index
     */
    public function index()
    {
        $this->authorize('view', Order::class);

        return view('admin.reports.index');
    }

    /**
     * Order reports
     */
    public function orders(Request $request)
    {
        $this->authorize('view', Order::class);

        $query = Order::with(['customer', 'branch', 'items.product']);

        // Filters
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest('order_date')->get();

        if ($request->has('export')) {
            return $this->exportOrders($orders);
        }

        return view('admin.reports.orders', compact('orders'));
    }

    /**
     * Sales reports
     */
    public function sales(Request $request)
    {
        $this->authorize('view', PosSale::class);

        $query = PosSale::with(['customer', 'branch', 'items.product']);

        // Filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->latest('sale_date')->get();

        if ($request->has('export')) {
            return $this->exportSales($sales);
        }

        return view('admin.reports.sales', compact('sales'));
    }

    /**
     * Inventory reports
     */
    public function inventory(Request $request)
    {
        $this->authorize('view', Inventory::class);

        $query = Inventory::with(['product', 'branch']);

        // Filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('low_stock')) {
            $query->whereHas('product', function ($q) {
                $q->whereColumn('inventories.quantity', '<=', 'products.low_stock_alert');
            });
        }

        $inventories = $query->get();

        if ($request->has('export')) {
            return $this->exportInventory($inventories);
        }

        return view('admin.reports.inventory', compact('inventories'));
    }

    /**
     * Factory reports
     */
    public function factory(Request $request)
    {
        $this->authorize('view', FactoryProduction::class);

        $query = FactoryProduction::with(['product', 'order', 'steps.worker']);

        // Filters
        if ($request->filled('date_from')) {
            $query->where('production_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('production_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $productions = $query->latest('production_date')->get();

        if ($request->has('export')) {
            return $this->exportFactory($productions);
        }

        return view('admin.reports.factory', compact('productions'));
    }

    /**
     * HR reports
     */
    public function hr(Request $request)
    {
        $this->authorize('view', Employee::class);

        $reportType = $request->get('type', 'attendance'); // attendance, salary, leave

        if ($reportType === 'attendance') {
            return $this->attendanceReport($request);
        } elseif ($reportType === 'salary') {
            return $this->salaryReport($request);
        } elseif ($reportType === 'leave') {
            return $this->leaveReport($request);
        }

        return view('admin.reports.hr');
    }

    /**
     * Accounting reports
     */
    public function accounting(Request $request)
    {
        $this->authorize('view', ChartOfAccount::class);

        $reportType = $request->get('type', 'ledger'); // ledger, trial_balance, profit_loss

        if ($reportType === 'ledger') {
            return $this->ledgerReport($request);
        } elseif ($reportType === 'trial_balance') {
            return $this->trialBalanceReport($request);
        }

        return view('admin.reports.accounting');
    }

    /**
     * Export orders to Excel
     */
    protected function exportOrders($orders)
    {
        // This would use a dedicated Export class
        // For now, return a simple response
        return response()->json(['message' => 'Excel export functionality will be implemented']);
    }

    /**
     * Export sales to Excel
     */
    protected function exportSales($sales)
    {
        return response()->json(['message' => 'Excel export functionality will be implemented']);
    }

    /**
     * Export inventory to Excel
     */
    protected function exportInventory($inventories)
    {
        return response()->json(['message' => 'Excel export functionality will be implemented']);
    }

    /**
     * Export factory to Excel
     */
    protected function exportFactory($productions)
    {
        return response()->json(['message' => 'Excel export functionality will be implemented']);
    }

    /**
     * Attendance report
     */
    protected function attendanceReport(Request $request)
    {
        $query = \App\Models\Attendance::with(['employee', 'branch']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->where('attendance_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('attendance_date', '<=', $request->date_to);
        }

        $attendances = $query->latest('attendance_date')->get();

        if ($request->has('export')) {
            return $this->exportAttendance($attendances);
        }

        return view('admin.reports.attendance', compact('attendances'));
    }

    /**
     * Salary report
     */
    protected function salaryReport(Request $request)
    {
        $query = SalaryPayment::with(['employee']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest('payment_date')->get();

        if ($request->has('export')) {
            return $this->exportSalary($payments);
        }

        return view('admin.reports.salary', compact('payments'));
    }

    /**
     * Leave report
     */
    protected function leaveReport(Request $request)
    {
        $query = \App\Models\Leave::with(['employee']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date_from')) {
            $query->where('leave_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('leave_date', '<=', $request->date_to);
        }

        $leaves = $query->latest('leave_date')->get();

        if ($request->has('export')) {
            return $this->exportLeave($leaves);
        }

        return view('admin.reports.leave', compact('leaves'));
    }

    /**
     * Ledger report
     */
    protected function ledgerReport(Request $request)
    {
        $query = Ledger::with(['account', 'branch']);

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $ledgers = $query->latest('transaction_date')->get();

        if ($request->has('export')) {
            return $this->exportLedger($ledgers);
        }

        return view('admin.reports.ledger', compact('ledgers'));
    }

    /**
     * Trial balance report
     */
    protected function trialBalanceReport(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->toDateString());

        $accounts = ChartOfAccount::with(['ledgerEntries' => function ($query) use ($asOfDate) {
            $query->where('transaction_date', '<=', $asOfDate);
        }])->where('is_active', true)->get();

        $trialBalance = $accounts->map(function ($account) {
            $debit = $account->ledgerEntries->sum('debit');
            $credit = $account->ledgerEntries->sum('credit');
            $balance = $debit - $credit;

            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_type' => $account->account_type,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
            ];
        });

        if ($request->has('export')) {
            return $this->exportTrialBalance($trialBalance);
        }

        return view('admin.reports.trial-balance', compact('trialBalance', 'asOfDate'));
    }

    /**
     * Export methods (placeholder - will be implemented with Excel exports)
     */
    protected function exportAttendance($attendances) { return response()->json(['message' => 'Export will be implemented']); }
    protected function exportSalary($payments) { return response()->json(['message' => 'Export will be implemented']); }
    protected function exportLeave($leaves) { return response()->json(['message' => 'Export will be implemented']); }
    protected function exportLedger($ledgers) { return response()->json(['message' => 'Export will be implemented']); }
    protected function exportTrialBalance($trialBalance) { return response()->json(['message' => 'Export will be implemented']); }
}

