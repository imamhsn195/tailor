<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Enums\MembershipType;
use App\Rules\EnumRule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:customer.view')->only(['index', 'show']);
        $this->middleware('permission:customer.create')->only(['create', 'store']);
        $this->middleware('permission:customer.edit')->only(['edit', 'update']);
        $this->middleware('permission:customer.delete')->only(['destroy']);
    }

    /**
     * Display a listing of memberships
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $query = Membership::withCount('customers');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $memberships = $query->latest()->paginate(15);

        return view('admin.memberships.index', compact('memberships'));
    }

    /**
     * Show the form for creating a new membership
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        return view('admin.memberships.create');
    }

    /**
     * Store a newly created membership
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', new EnumRule(MembershipType::class, 'Type')],
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            Membership::create($validated);

            DB::commit();

            return redirect()->route('admin.memberships.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified membership
     */
    public function show(Membership $membership)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $membership->load('customers');

        return view('admin.memberships.show', compact('membership'));
    }

    /**
     * Show the form for editing the specified membership
     */
    public function edit(Membership $membership)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        return view('admin.memberships.edit', compact('membership'));
    }

    /**
     * Update the specified membership
     */
    public function update(Request $request, Membership $membership)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', new EnumRule(MembershipType::class, 'Type')],
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $membership->update($validated);

            DB::commit();

            return redirect()->route('admin.memberships.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified membership
     */
    public function destroy(Membership $membership)
    {
        abort_unless(auth()->user()?->can('customer.delete'), 403);

        DB::beginTransaction();
        try {
            $membership->delete();
            DB::commit();

            return redirect()->route('admin.memberships.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
