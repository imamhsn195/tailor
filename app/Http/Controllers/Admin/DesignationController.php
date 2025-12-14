<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DesignationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:designation.view')->only(['index', 'show']);
        $this->middleware('permission:designation.create')->only(['create', 'store']);
        $this->middleware('permission:designation.edit')->only(['edit', 'update']);
        $this->middleware('permission:designation.delete')->only(['destroy']);
    }

    /**
     * Display a listing of designations
     */
    public function index(Request $request)
    {
        $this->authorize('view', Designation::class);

        $query = Designation::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $designations = $query->latest()->paginate(15);

        return view('admin.designations.index', compact('designations'));
    }

    /**
     * Store a newly created designation
     */
    public function store(Request $request)
    {
        $this->authorize('create', Designation::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:designations,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Designation::create($validated);

        return redirect()->route('admin.designations.index')
            ->with('success', trans_common('created_successfully'));
    }

    /**
     * Update the specified designation
     */
    public function update(Request $request, Designation $designation)
    {
        $this->authorize('update', $designation);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:designations,name,' . $designation->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $designation->update($validated);

        return redirect()->route('admin.designations.index')
            ->with('success', trans_common('updated_successfully'));
    }

    /**
     * Remove the specified designation
     */
    public function destroy(Designation $designation)
    {
        $this->authorize('delete', $designation);

        $designation->delete();

        return redirect()->route('admin.designations.index')
            ->with('success', trans_common('deleted_successfully'));
    }
}

