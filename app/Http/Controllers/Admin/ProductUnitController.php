<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ProductUnitController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:product_unit.view')->only(['index', 'show']);
        $this->middleware('permission:product_unit.create')->only(['create', 'store']);
        $this->middleware('permission:product_unit.edit')->only(['edit', 'update']);
        $this->middleware('permission:product_unit.delete')->only(['destroy']);
    }

    /**
     * Display a listing of product units
     */
    public function index(Request $request)
    {
        $this->authorize('view', ProductUnit::class);

        $query = ProductUnit::withCount('products');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('abbreviation', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $units = $query->latest()->paginate(15);

        return view('admin.product-units.index', compact('units'));
    }

    /**
     * Show the form for creating a new unit
     */
    public function create()
    {
        $this->authorize('create', ProductUnit::class);

        return view('admin.product-units.create');
    }

    /**
     * Store a newly created unit
     */
    public function store(Request $request)
    {
        $this->authorize('create', ProductUnit::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10|unique:product_units,abbreviation',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $unit = ProductUnit::create($validated);
            DB::commit();

            return redirect()->route('admin.product-units.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified unit
     */
    public function show(ProductUnit $productUnit)
    {
        $this->authorize('view', $productUnit);

        $productUnit->load('products');

        return view('admin.product-units.show', compact('productUnit'));
    }

    /**
     * Show the form for editing the specified unit
     */
    public function edit(ProductUnit $productUnit)
    {
        $this->authorize('edit', $productUnit);

        return view('admin.product-units.edit', compact('productUnit'));
    }

    /**
     * Update the specified unit
     */
    public function update(Request $request, ProductUnit $productUnit)
    {
        $this->authorize('update', $productUnit);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10|unique:product_units,abbreviation,' . $productUnit->id,
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $productUnit->update($validated);
            DB::commit();

            return redirect()->route('admin.product-units.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified unit
     */
    public function destroy(ProductUnit $productUnit)
    {
        $this->authorize('delete', $productUnit);

        if ($productUnit->products()->count() > 0) {
            return back()->with('error', 'Cannot delete unit with associated products.');
        }

        DB::beginTransaction();
        try {
            $productUnit->delete();
            DB::commit();

            return redirect()->route('admin.product-units.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}

