<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkerCategory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class WorkerCategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:worker_category.view')->only(['index', 'show']);
        $this->middleware('permission:worker_category.create')->only(['create', 'store']);
        $this->middleware('permission:worker_category.edit')->only(['edit', 'update']);
        $this->middleware('permission:worker_category.delete')->only(['destroy']);
    }

    /**
     * Display a listing of worker categories
     */
    public function index(Request $request)
    {
        $this->authorize('view', WorkerCategory::class);

        $query = WorkerCategory::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->latest()->paginate(15);

        return view('admin.worker-categories.index', compact('categories'));
    }

    /**
     * Store a newly created worker category
     */
    public function store(Request $request)
    {
        $this->authorize('create', WorkerCategory::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:worker_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        WorkerCategory::create($validated);

        return redirect()->route('admin.worker-categories.index')
            ->with('success', trans_common('created_successfully'));
    }

    /**
     * Update the specified worker category
     */
    public function update(Request $request, WorkerCategory $workerCategory)
    {
        $this->authorize('update', $workerCategory);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:worker_categories,name,' . $workerCategory->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $workerCategory->update($validated);

        return redirect()->route('admin.worker-categories.index')
            ->with('success', trans_common('updated_successfully'));
    }

    /**
     * Remove the specified worker category
     */
    public function destroy(WorkerCategory $workerCategory)
    {
        $this->authorize('delete', $workerCategory);

        $workerCategory->delete();

        return redirect()->route('admin.worker-categories.index')
            ->with('success', trans_common('deleted_successfully'));
    }
}

