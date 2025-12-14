<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:department.view')->only(['index', 'show']);
        $this->middleware('permission:department.create')->only(['create', 'store']);
        $this->middleware('permission:department.edit')->only(['edit', 'update']);
        $this->middleware('permission:department.delete')->only(['destroy']);
    }

    /**
     * Display a listing of departments
     */
    public function index(Request $request)
    {
        $this->authorize('view', Department::class);

        $query = Department::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $departments = $query->latest()->paginate(15);

        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $this->authorize('create', Department::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', trans_common('created_successfully'));
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department)
    {
        $this->authorize('update', $department);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', trans_common('updated_successfully'));
    }

    /**
     * Remove the specified department
     */
    public function destroy(Department $department)
    {
        $this->authorize('delete', $department);

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', trans_common('deleted_successfully'));
    }
}

