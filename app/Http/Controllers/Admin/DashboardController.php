<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Get tenant context
        $tenant = \Spatie\Multitenancy\Models\Tenant::current();
        
        // Dashboard statistics (placeholder - will be populated with actual data)
        $stats = [
            'total_orders' => 0,
            'total_sales' => 0,
            'total_customers' => 0,
            'total_products' => 0,
            'pending_orders' => 0,
            'today_sales' => 0,
        ];

        // Get branch-wise statistics if tenant has branches
        $branchStats = [];

        return view('admin.dashboard.index', compact('stats', 'branchStats'));
    }
}
