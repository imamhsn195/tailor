<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:product_category.view')->only(['index', 'show']);
        $this->middleware('permission:product_category.create')->only(['create', 'store']);
        $this->middleware('permission:product_category.edit')->only(['edit', 'update']);
        $this->middleware('permission:product_category.delete')->only(['destroy']);
    }

    /**
     * Display a listing of product categories
     */
    public function index(Request $request)
    {
        $this->authorize('view', ProductCategory::class);

        $query = ProductCategory::withCount('products');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->latest()->paginate(15);

        return view('admin.product-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $this->authorize('create', ProductCategory::class);

        return view('admin.product-categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $this->authorize('create', ProductCategory::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $category = ProductCategory::create($validated);
            DB::commit();

            return redirect()->route('admin.product-categories.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified category
     */
    public function show(ProductCategory $productCategory)
    {
        $this->authorize('view', $productCategory);

        $productCategory->load('products');

        return view('admin.product-categories.show', compact('productCategory'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(ProductCategory $productCategory)
    {
        $this->authorize('edit', $productCategory);

        return view('admin.product-categories.edit', compact('productCategory'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $this->authorize('update', $productCategory);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name,' . $productCategory->id,
            'slug' => 'nullable|string|max:255|unique:product_categories,slug,' . $productCategory->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $productCategory->update($validated);
            DB::commit();

            return redirect()->route('admin.product-categories.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(ProductCategory $productCategory)
    {
        $this->authorize('delete', $productCategory);

        if ($productCategory->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated products.');
        }

        DB::beginTransaction();
        try {
            $productCategory->delete();
            DB::commit();

            return redirect()->route('admin.product-categories.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}

