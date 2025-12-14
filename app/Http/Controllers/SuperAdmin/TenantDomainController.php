<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantDomainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\EnsureSuperAdmin::class);
    }

    /**
     * Display domains for a tenant
     */
    public function index(Tenant $tenant)
    {
        $domains = $tenant->domains()->latest()->get();

        return view('super-admin.tenant-domains.index', compact('tenant', 'domains'));
    }

    /**
     * Store a new domain for tenant
     */
    public function store(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:tenant_domains,domain',
        ]);

        DB::beginTransaction();
        try {
            // If this is the first domain, make it primary
            $isPrimary = $tenant->domains()->count() === 0;

            $domain = TenantDomain::create([
                'tenant_id' => $tenant->id,
                'domain' => $validated['domain'],
                'is_primary' => $isPrimary,
                'is_verified' => false,
            ]);

            DB::commit();

            return redirect()->route('super-admin.tenant-domains.index', $tenant)
                ->with('success', 'Domain added successfully. Please verify the domain.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add domain: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Set domain as primary
     */
    public function setPrimary(Tenant $tenant, TenantDomain $domain)
    {
        DB::beginTransaction();
        try {
            // Unset all primary domains for this tenant
            $tenant->domains()->update(['is_primary' => false]);

            // Set this domain as primary
            $domain->update(['is_primary' => true]);

            DB::commit();

            return redirect()->route('super-admin.tenant-domains.index', $tenant)
                ->with('success', 'Primary domain updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update primary domain: ' . $e->getMessage());
        }
    }

    /**
     * Verify domain
     */
    public function verify(Tenant $tenant, TenantDomain $domain)
    {
        // In a real implementation, you would check DNS records here
        // For now, we'll just mark it as verified
        $domain->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return redirect()->route('super-admin.tenant-domains.index', $tenant)
            ->with('success', 'Domain verified successfully');
    }

    /**
     * Remove domain
     */
    public function destroy(Tenant $tenant, TenantDomain $domain)
    {
        // Don't allow removing primary domain if it's the only one
        if ($domain->is_primary && $tenant->domains()->count() === 1) {
            return back()->with('error', 'Cannot remove the only primary domain');
        }

        $domain->delete();

        return redirect()->route('super-admin.tenant-domains.index', $tenant)
            ->with('success', 'Domain removed successfully');
    }
}

