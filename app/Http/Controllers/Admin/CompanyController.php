<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:company.view')->only(['index', 'show']);
        $this->middleware('permission:company.create')->only(['create', 'store']);
        $this->middleware('permission:company.edit')->only(['edit', 'update']);
        $this->middleware('permission:company.delete')->only(['destroy']);
    }

    /**
     * Display a listing of companies
     */
    public function index(Request $request)
    {
        $this->authorize('view', Company::class);

        $query = Company::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $companies = $query->withCount('branches')->latest()->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company
     */
    public function create()
    {
        $this->authorize('create', Company::class);

        return view('admin.companies.create');
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        $this->authorize('create', Company::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'invoice_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'company_registration_no' => 'nullable|string|max:255',
            'company_tin_no' => 'nullable|string|max:255',
            'e_bin' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $company = Company::create($validated);
            DB::commit();

            return redirect()->route('admin.companies.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        $this->authorize('view', $company);

        $company->load('branches');

        return view('admin.companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified company
     */
    public function edit(Company $company)
    {
        $this->authorize('edit', $company);

        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'invoice_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'company_registration_no' => 'nullable|string|max:255',
            'company_tin_no' => 'nullable|string|max:255',
            'e_bin' => 'nullable|string|max:255',
            'bin' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $company->update($validated);
            DB::commit();

            return redirect()->route('admin.companies.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);

        DB::beginTransaction();
        try {
            $company->delete();
            DB::commit();

            return redirect()->route('admin.companies.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}

