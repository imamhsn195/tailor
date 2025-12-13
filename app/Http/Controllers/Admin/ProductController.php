<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:product.view')->only(['index', 'show']);
        $this->middleware('permission:product.create')->only(['create', 'store']);
        $this->middleware('permission:product.edit')->only(['edit', 'update']);
        $this->middleware('permission:product.delete')->only(['destroy']);
    }

    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $this->authorize('view', Product::class);

        $query = Product::with(['category', 'unit']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(15);
        $categories = ProductCategory::where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = ProductCategory::where('is_active', true)->get();
        $units = ProductUnit::where('is_active', true)->get();

        return view('admin.products.create', compact('categories', 'units'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'required|exists:product_units,id',
            'brand' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'fabric_width' => 'nullable|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'vat_type' => 'nullable|in:inclusive,exclusive',
            'low_stock_alert' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'generate_barcode' => 'boolean',
            'generate_qr_code' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Generate barcode if requested
            if ($request->boolean('generate_barcode') && empty($validated['barcode'] ?? null)) {
                $validated['barcode'] = $this->generateBarcode();
            }

            // Generate QR code if requested
            if ($request->boolean('generate_qr_code') && empty($validated['qr_code'] ?? null)) {
                $validated['qr_code'] = $this->generateQrCode();
            }

            $product = Product::create($validated);

            // Handle sizes if provided
            if ($request->filled('sizes')) {
                foreach ($request->sizes as $size) {
                    $product->sizes()->create([
                        'size' => $size,
                        'sort_order' => 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['category', 'unit', 'sizes']);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $this->authorize('edit', $product);

        $product->load('sizes');
        $categories = ProductCategory::where('is_active', true)->get();
        $units = ProductUnit::where('is_active', true)->get();

        return view('admin.products.edit', compact('product', 'categories', 'units'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'required|exists:product_units,id',
            'brand' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'fabric_width' => 'nullable|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'vat_type' => 'nullable|in:inclusive,exclusive',
            'low_stock_alert' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $product->update($validated);

            // Update sizes if provided
            if ($request->has('sizes')) {
                $product->sizes()->delete();
                foreach ($request->sizes as $size) {
                    if (!empty($size)) {
                        $product->sizes()->create([
                            'size' => $size,
                            'sort_order' => 0,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        DB::beginTransaction();
        try {
            $product->delete();
            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Generate unique barcode
     */
    protected function generateBarcode(): string
    {
        do {
            $barcode = 'PRD' . strtoupper(Str::random(10));
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Generate unique QR code
     */
    protected function generateQrCode(): string
    {
        do {
            $qrCode = 'QR' . strtoupper(Str::random(12));
        } while (Product::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}

