<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    protected TenantProvisioningService $provisioningService;

    public function __construct(TenantProvisioningService $provisioningService)
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\EnsureSuperAdmin::class);
        $this->provisioningService = $provisioningService;
    }

    /**
     * Display a listing of tenants
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['activeSubscription.plan']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhere('database_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tenants = $query->latest()->paginate(15);

        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'inactive' => Tenant::where('status', 'inactive')->count(),
        ];

        return view('super-admin.tenants.index', compact('tenants', 'stats'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return view('super-admin.tenants.create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'trial_days' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate unique database name
            $databaseName = 'tenant_' . time() . '_' . Str::random(8);

            // Create tenant
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'domain' => $validated['domain'],
                'database_name' => $databaseName,
                'status' => 'active',
                'trial_ends_at' => $validated['trial_days'] ? now()->addDays($validated['trial_days']) : null,
            ]);

            // Provision tenant database
            $this->provisioningService->provision($tenant, [
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            DB::commit();

            return redirect()->route('super-admin.tenants.show', $tenant)
                ->with('success', 'Tenant created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create tenant: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['activeSubscription.plan', 'domains']);

        // Get tenant database stats
        $dbStats = $this->getDatabaseStats($tenant);

        return view('super-admin.tenants.show', compact('tenant', 'dbStats'));
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        return view('super-admin.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain,' . $tenant->id,
            'status' => 'required|in:active,suspended,inactive',
        ]);

        $tenant->update($validated);

        return redirect()->route('super-admin.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully');
    }

    /**
     * Suspend tenant
     */
    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);

        return redirect()->route('super-admin.tenants.show', $tenant)
            ->with('success', 'Tenant suspended successfully');
    }

    /**
     * Activate tenant
     */
    public function activate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);

        return redirect()->route('super-admin.tenants.show', $tenant)
            ->with('success', 'Tenant activated successfully');
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant)
    {
        DB::beginTransaction();
        try {
            // Drop tenant database
            DB::statement("DROP DATABASE IF EXISTS `{$tenant->database_name}`");

            // Delete tenant record
            $tenant->delete();

            DB::commit();

            return redirect()->route('super-admin.tenants.index')
                ->with('success', 'Tenant deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete tenant: ' . $e->getMessage());
        }
    }

    /**
     * Get database statistics for tenant
     */
    protected function getDatabaseStats(Tenant $tenant): array
    {
        try {
            $connection = DB::connection('landlord');
            $dbName = $tenant->database_name;

            // Get table count
            $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [$dbName])[0]->count ?? 0;

            // Get database size
            $dbSize = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = ?", [$dbName])[0]->size_mb ?? 0;

            return [
                'table_count' => $tableCount,
                'size_mb' => $dbSize,
            ];
        } catch (\Exception $e) {
            return [
                'table_count' => 0,
                'size_mb' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }
}

