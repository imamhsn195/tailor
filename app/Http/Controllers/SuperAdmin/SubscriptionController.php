<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\EnsureSuperAdmin::class);
    }

    /**
     * Display a listing of subscriptions
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['tenant', 'plan']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('tenant', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate(15);

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
        ];

        return view('super-admin.subscriptions.index', compact('subscriptions', 'stats'));
    }

    /**
     * Display the specified subscription
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan']);

        return view('super-admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Cancel subscription
     */
    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('super-admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled successfully');
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
        ]);

        return redirect()->route('super-admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription reactivated successfully');
    }
}

