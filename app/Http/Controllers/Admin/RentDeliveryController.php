<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentDelivery;
use App\Models\RentOrder;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RentDeliveryController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:rent_delivery.view')->only(['index', 'show']);
        $this->middleware('permission:rent_delivery.create')->only(['create', 'store']);
    }

    /**
     * Display a listing of rent deliveries
     */
    public function index(Request $request)
    {
        $this->authorize('view', RentDelivery::class);

        $query = RentDelivery::with(['rentOrder.customer', 'rentOrder.branch']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('delivery_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('delivery_date', '<=', $request->date_to);
        }

        $deliveries = $query->latest('delivery_date')->paginate(15);

        return view('admin.rent-deliveries.index', compact('deliveries'));
    }

    /**
     * Show the form for creating a new delivery
     */
    public function create(Request $request)
    {
        $this->authorize('create', RentDelivery::class);

        $rentOrderId = $request->input('rent_order_id');
        $rentOrder = $rentOrderId ? RentOrder::with(['items.product', 'customer'])->findOrFail($rentOrderId) : null;
        $rentOrders = RentOrder::where('status', 'active')
            ->with('customer')
            ->latest('rent_date')
            ->get();

        return view('admin.rent-deliveries.create', compact('rentOrder', 'rentOrders'));
    }

    /**
     * Store a newly created delivery
     */
    public function store(Request $request)
    {
        $this->authorize('create', RentDelivery::class);

        $validated = $request->validate([
            'rent_order_id' => 'required|exists:rent_orders,id',
            'delivery_date' => 'required|date',
            'delivery_status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        RentDelivery::create([
            'rent_order_id' => $validated['rent_order_id'],
            'delivery_date' => $validated['delivery_date'],
            'delivery_status' => $validated['delivery_status'],
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.rent-deliveries.index')
            ->with('success', trans_common('created_successfully'));
    }

    /**
     * Display the specified delivery
     */
    public function show(RentDelivery $rentDelivery)
    {
        $this->authorize('view', $rentDelivery);

        $rentDelivery->load(['rentOrder.customer', 'rentOrder.branch', 'rentOrder.items.product', 'user']);

        return view('admin.rent-deliveries.show', compact('rentDelivery'));
    }
}

