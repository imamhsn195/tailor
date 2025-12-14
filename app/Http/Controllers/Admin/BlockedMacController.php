<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedMac;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class BlockedMacController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:settings.view')->only(['index', 'show']);
        $this->middleware('permission:settings.edit')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of blocked MACs
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        $query = BlockedMac::with('blockedBy');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mac_address', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $blockedMacs = $query->latest()->paginate(15);

        return view('admin.blocked-macs.index', compact('blockedMacs'));
    }

    /**
     * Show the form for creating a new blocked MAC
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        return view('admin.blocked-macs.create');
    }

    /**
     * Store a newly created blocked MAC
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        $validated = $request->validate([
            'mac_address' => 'required|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/|unique:blocked_macs,mac_address',
            'reason' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $validated['blocked_by'] = auth()->id();
            BlockedMac::create($validated);

            DB::commit();

            return redirect()->route('admin.blocked-macs.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified blocked MAC
     */
    public function show(BlockedMac $blockedMac)
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        $blockedMac->load('blockedBy');

        return view('admin.blocked-macs.show', compact('blockedMac'));
    }

    /**
     * Show the form for editing the specified blocked MAC
     */
    public function edit(BlockedMac $blockedMac)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        return view('admin.blocked-macs.edit', compact('blockedMac'));
    }

    /**
     * Update the specified blocked MAC
     */
    public function update(Request $request, BlockedMac $blockedMac)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        $validated = $request->validate([
            'mac_address' => 'required|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/|unique:blocked_macs,mac_address,' . $blockedMac->id,
            'reason' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $blockedMac->update($validated);

            DB::commit();

            return redirect()->route('admin.blocked-macs.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified blocked MAC
     */
    public function destroy(BlockedMac $blockedMac)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        DB::beginTransaction();
        try {
            $blockedMac->delete();
            DB::commit();

            return redirect()->route('admin.blocked-macs.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
