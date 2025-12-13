<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:branch.view')->only(['index', 'show']);
        $this->middleware('permission:branch.create')->only(['create', 'store']);
        $this->middleware('permission:branch.edit')->only(['edit', 'update']);
        $this->middleware('permission:branch.delete')->only(['destroy']);
    }

    /**
     * Display a listing of branches
     */
    public function index(Request $request)
    {
        $this->authorize('view', Branch::class);

        $query = Branch::with('company');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('branch_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $branches = $query->latest()->paginate(15);
        $companies = Company::all();

        return view('admin.branches.index', compact('branches', 'companies'));
    }

    /**
     * Show the form for creating a new branch
     */
    public function create()
    {
        $this->authorize('create', Branch::class);

        $companies = Company::all();
        $modules = ['pos' => 'POS', 'tailor' => 'Tailor'];

        return view('admin.branches.create', compact('companies', 'modules'));
    }

    /**
     * Store a newly created branch
     */
    public function store(Request $request)
    {
        $this->authorize('create', Branch::class);

        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'required|string|max:255|unique:branches',
            'name' => 'required|string|max:255',
            'e_bin' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'trade_license_no' => 'nullable|string|max:255',
            'modules' => 'nullable|array',
            'modules.*' => 'in:pos,tailor',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $branch = Branch::create($validated);
            DB::commit();

            return redirect()->route('admin.branches.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified branch
     */
    public function show(Branch $branch)
    {
        $this->authorize('view', $branch);

        $branch->load('company', 'users');

        return view('admin.branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified branch
     */
    public function edit(Branch $branch)
    {
        $this->authorize('edit', $branch);

        $companies = Company::all();
        $modules = ['pos' => 'POS', 'tailor' => 'Tailor'];

        return view('admin.branches.edit', compact('branch', 'companies', 'modules'));
    }

    /**
     * Update the specified branch
     */
    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);

        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'required|string|max:255|unique:branches,branch_id,' . $branch->id,
            'name' => 'required|string|max:255',
            'e_bin' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'trade_license_no' => 'nullable|string|max:255',
            'modules' => 'nullable|array',
            'modules.*' => 'in:pos,tailor',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $branch->update($validated);
            DB::commit();

            return redirect()->route('admin.branches.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified branch
     */
    public function destroy(Branch $branch)
    {
        $this->authorize('delete', $branch);

        DB::beginTransaction();
        try {
            $branch->delete();
            DB::commit();

            return redirect()->route('admin.branches.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}

