<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosSale;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FactoryProduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        // Get all branches for filter
        $branches = Branch::where('is_active', true)->get();

        // Total Collections (Orders + POS Sales)
        $totalCollections = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('paid_amount') + 
            PosSale::whereBetween('sale_date', [$dateFrom, $dateTo])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // Total Orders
        $totalOrders = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        // Total Sales (POS)
        $totalSales = PosSale::whereBetween('sale_date', [$dateFrom, $dateTo])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        // Total Expenses
        $totalExpenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        // Branch-wise Collections
        $branchCollections = Branch::where('is_active', true)
            ->get()
            ->map(function ($branch) use ($dateFrom, $dateTo) {
                $orderCollection = Order::where('branch_id', $branch->id)
                    ->whereBetween('order_date', [$dateFrom, $dateTo])
                    ->sum('paid_amount');

                $posCollection = PosSale::where('branch_id', $branch->id)
                    ->whereBetween('sale_date', [$dateFrom, $dateTo])
                    ->sum('total_amount');

                return (object) [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'order_collection' => $orderCollection,
                    'pos_collection' => $posCollection,
                    'total_collection' => $orderCollection + $posCollection,
                ];
            })
            ->sortByDesc('total_collection')
            ->values();

        // Branch-wise Orders
        $branchOrders = Branch::where('is_active', true)
            ->get()
            ->map(function ($branch) use ($dateFrom, $dateTo) {
                $orders = Order::where('branch_id', $branch->id)
                    ->whereBetween('order_date', [$dateFrom, $dateTo])
                    ->get();

                return (object) [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'order_count' => $orders->count(),
                    'order_amount' => $orders->sum('total_amount'),
                ];
            })
            ->sortByDesc('order_count')
            ->values();

        // Branch-wise Expenses
        $branchExpenses = Branch::where('is_active', true)
            ->get()
            ->map(function ($branch) use ($dateFrom, $dateTo) {
                $totalExpense = Expense::where('branch_id', $branch->id)
                    ->whereBetween('expense_date', [$dateFrom, $dateTo])
                    ->sum('amount');

                return (object) [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'total_expense' => $totalExpense,
                ];
            })
            ->sortByDesc('total_expense')
            ->values();

        // Recent Orders
        $recentOrders = Order::with(['customer', 'branch'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest('order_date')
            ->limit(10)
            ->get();

        // Recent Sales
        $recentSales = PosSale::with(['customer', 'branch'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest('sale_date')
            ->limit(10)
            ->get();

        // Low Stock Products
        $lowStockProducts = Inventory::with(['product', 'branch'])
            ->whereHas('product', function ($q) {
                $q->whereColumn('inventories.quantity', '<=', 'products.low_stock_alert');
            })
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get();

        // Pending Orders
        $pendingOrders = Order::where('status', 'pending')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        // Total Customers
        $totalCustomers = Customer::where('is_active', true)->count();

        // Total Employees
        $totalEmployees = Employee::where('is_active', true)->count();

        // Active Productions
        $activeProductions = FactoryProduction::whereIn('status', ['pending', 'in_progress'])
            ->count();

        return view('admin.dashboard.index', compact(
            'totalCollections',
            'totalOrders',
            'totalSales',
            'totalExpenses',
            'branchCollections',
            'branchOrders',
            'branchExpenses',
            'recentOrders',
            'recentSales',
            'lowStockProducts',
            'pendingOrders',
            'totalCustomers',
            'totalEmployees',
            'activeProductions',
            'branches',
            'branchId',
            'dateFrom',
            'dateTo'
        ));
    }
}
