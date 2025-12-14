<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
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
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $query = Customer::with('memberships');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('customer_id', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->latest()->paginate(15);
        $memberships = Membership::where('is_active', true)->get();

        return view('admin.customers.index', compact('customers', 'memberships'));
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        $memberships = Membership::where('is_active', true)->get();

        return view('admin.customers.create', compact('memberships'));
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('customer.create'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'comments' => 'nullable|string',
            'is_active' => 'boolean',
            'memberships' => 'nullable|array',
            'memberships.*' => 'exists:memberships,id',
        ]);

        DB::beginTransaction();
        try {
            // Generate customer ID if not provided
            if (empty($validated['customer_id'] ?? null)) {
                do {
                    $customerId = 'CUST' . strtoupper(Str::random(8));
                } while (Customer::where('customer_id', $customerId)->exists());
                $validated['customer_id'] = $customerId;
            }

            $customer = Customer::create($validated);

            // Attach memberships
            if ($request->filled('memberships')) {
                $membershipData = [];
                foreach ($request->memberships as $membershipId) {
                    $membershipData[$membershipId] = [
                        'joined_at' => now(),
                        'expires_at' => null,
                    ];
                }
                $customer->memberships()->sync($membershipData);
            }

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        abort_unless(auth()->user()?->can('customer.view'), 403);

        $customer->load(['memberships', 'orders', 'posSales', 'comments.user']);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit(Customer $customer)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $customer->load('memberships');
        $memberships = Membership::where('is_active', true)->get();

        return view('admin.customers.edit', compact('customer', 'memberships'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'comments' => 'nullable|string',
            'is_active' => 'boolean',
            'memberships' => 'nullable|array',
            'memberships.*' => 'exists:memberships,id',
        ]);

        DB::beginTransaction();
        try {
            $customer->update($validated);

            // Sync memberships
            if ($request->has('memberships')) {
                $membershipData = [];
                foreach ($request->memberships as $membershipId) {
                    $membershipData[$membershipId] = [
                        'joined_at' => now(),
                        'expires_at' => null,
                    ];
                }
                $customer->memberships()->sync($membershipData);
            } else {
                $customer->memberships()->detach();
            }

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        abort_unless(auth()->user()?->can('customer.delete'), 403);

        DB::beginTransaction();
        try {
            $customer->delete();
            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Add a comment to the customer
     */
    public function addComment(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()?->can('customer.edit'), 403);

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $customer->comments()->create([
                'comment' => $validated['comment'],
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return back()->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
