<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class BlockedIpController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:settings.view')->only(['index', 'show']);
        $this->middleware('permission:settings.edit')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of blocked IPs
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        $query = BlockedIp::with('blockedBy');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $blockedIps = $query->latest()->paginate(15);

        return view('admin.blocked-ips.index', compact('blockedIps'));
    }

    /**
     * Show the form for creating a new blocked IP
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        return view('admin.blocked-ips.create');
    }

    /**
     * Store a newly created blocked IP
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        $validated = $request->validate([
            'ip_address' => 'required|ip|unique:blocked_ips,ip_address',
            'reason' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $validated['blocked_by'] = auth()->id();
            BlockedIp::create($validated);

            DB::commit();

            return redirect()->route('admin.blocked-ips.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified blocked IP
     */
    public function show(BlockedIp $blockedIp)
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        $blockedIp->load('blockedBy');

        return view('admin.blocked-ips.show', compact('blockedIp'));
    }

    /**
     * Show the form for editing the specified blocked IP
     */
    public function edit(BlockedIp $blockedIp)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        return view('admin.blocked-ips.edit', compact('blockedIp'));
    }

    /**
     * Update the specified blocked IP
     */
    public function update(Request $request, BlockedIp $blockedIp)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        $validated = $request->validate([
            'ip_address' => 'required|ip|unique:blocked_ips,ip_address,' . $blockedIp->id,
            'reason' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $blockedIp->update($validated);

            DB::commit();

            return redirect()->route('admin.blocked-ips.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified blocked IP
     */
    public function destroy(BlockedIp $blockedIp)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        DB::beginTransaction();
        try {
            $blockedIp->delete();
            DB::commit();

            return redirect()->route('admin.blocked-ips.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
