<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemHealthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\EnsureSuperAdmin::class);
    }

    /**
     * Display system health dashboard
     */
    public function index()
    {
        $stats = [
            'tenants' => [
                'total' => Tenant::count(),
                'active' => Tenant::where('status', 'active')->count(),
                'suspended' => Tenant::where('status', 'suspended')->count(),
            ],
            'subscriptions' => [
                'total' => Subscription::count(),
                'active' => Subscription::where('status', 'active')->count(),
                'expiring_soon' => Subscription::where('status', 'active')
                    ->where('current_period_end', '<=', now()->addDays(7))
                    ->count(),
            ],
            'databases' => $this->getDatabaseStats(),
            'storage' => $this->getStorageStats(),
            'cache' => $this->getCacheStats(),
        ];

        $recentTenants = Tenant::latest()->take(5)->get();
        $recentSubscriptions = Subscription::with('tenant')->latest()->take(5)->get();

        return view('super-admin.system-health.index', compact('stats', 'recentTenants', 'recentSubscriptions'));
    }

    /**
     * Get database statistics
     */
    protected function getDatabaseStats(): array
    {
        try {
            $landlordDb = config('database.connections.landlord.database');
            
            // Get landlord database size
            $landlordSize = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [$landlordDb])[0]->size_mb ?? 0;

            // Get total tenant databases count and size
            $tenantDbs = Tenant::pluck('database_name');
            $totalTenantSize = 0;
            
            foreach ($tenantDbs as $dbName) {
                try {
                    $size = DB::select("
                        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                        FROM information_schema.tables 
                        WHERE table_schema = ?
                    ", [$dbName])[0]->size_mb ?? 0;
                    $totalTenantSize += $size;
                } catch (\Exception $e) {
                    // Skip if database doesn't exist
                }
            }

            return [
                'landlord_size_mb' => $landlordSize,
                'tenant_count' => $tenantDbs->count(),
                'total_tenant_size_mb' => $totalTenantSize,
                'total_size_mb' => $landlordSize + $totalTenantSize,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get storage statistics
     */
    protected function getStorageStats(): array
    {
        try {
            $disk = Storage::disk('local');
            $totalSize = 0;
            $fileCount = 0;

            foreach ($disk->allFiles() as $file) {
                $totalSize += $disk->size($file);
                $fileCount++;
            }

            return [
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'file_count' => $fileCount,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get cache statistics
     */
    protected function getCacheStats(): array
    {
        try {
            $driver = config('cache.default');
            
            return [
                'driver' => $driver,
                'status' => Cache::getStore()->getStore()->ping() ?? 'unknown',
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}

