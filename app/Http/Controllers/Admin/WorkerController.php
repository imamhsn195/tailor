<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerCategory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkerController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:worker.view')->only(['index', 'show']);
        $this->middleware('permission:worker.create')->only(['create', 'store']);
        $this->middleware('permission:worker.edit')->only(['edit', 'update']);
        $this->middleware('permission:worker.delete')->only(['destroy']);
    }

    /**
     * Display a listing of workers
     */
    public function index(Request $request)
    {
        $this->authorize('view', Worker::class);

        $query = Worker::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('worker_id', 'like', "%{$search}%")
                    ->orWhere('mobile_1', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $workers = $query->latest()->paginate(15);
        $categories = WorkerCategory::where('is_active', true)->get();

        return view('admin.workers.index', compact('workers', 'categories'));
    }

    /**
     * Show the form for creating a new worker
     */
    public function create()
    {
        $this->authorize('create', Worker::class);

        $categories = WorkerCategory::where('is_active', true)->get();

        return view('admin.workers.create', compact('categories'));
    }

    /**
     * Store a newly created worker
     */
    public function store(Request $request)
    {
        $this->authorize('create', Worker::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'address' => 'nullable|string',
            'nid_no' => 'nullable|string|max:255',
            'nid_photo' => 'nullable|image|max:2048',
            'mobile_1' => 'nullable|string|max:20',
            'mobile_2' => 'nullable|string|max:20',
            'mobile_3' => 'nullable|string|max:20',
            'home_mobile_1' => 'nullable|string|max:20',
            'home_mobile_2' => 'nullable|string|max:20',
            'home_mobile_3' => 'nullable|string|max:20',
            'reference_1' => 'nullable|string|max:255',
            'reference_2' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:worker_categories,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Generate worker ID
            if (empty($validated['worker_id'])) {
                $validated['worker_id'] = $this->generateWorkerId();
            }

            // Handle file uploads
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('workers/photos', 'public');
            }
            if ($request->hasFile('nid_photo')) {
                $validated['nid_photo'] = $request->file('nid_photo')->store('workers/nid', 'public');
            }

            Worker::create($validated);

            DB::commit();

            return redirect()->route('admin.workers.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified worker
     */
    public function show(Worker $worker)
    {
        $this->authorize('view', $worker);

        $worker->load(['category', 'jobAssignments.order', 'payments']);

        return view('admin.workers.show', compact('worker'));
    }

    /**
     * Show the form for editing the specified worker
     */
    public function edit(Worker $worker)
    {
        $this->authorize('edit', $worker);

        $categories = WorkerCategory::where('is_active', true)->get();

        return view('admin.workers.edit', compact('worker', 'categories'));
    }

    /**
     * Update the specified worker
     */
    public function update(Request $request, Worker $worker)
    {
        $this->authorize('update', $worker);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'address' => 'nullable|string',
            'nid_no' => 'nullable|string|max:255',
            'nid_photo' => 'nullable|image|max:2048',
            'mobile_1' => 'nullable|string|max:20',
            'mobile_2' => 'nullable|string|max:20',
            'mobile_3' => 'nullable|string|max:20',
            'home_mobile_1' => 'nullable|string|max:20',
            'home_mobile_2' => 'nullable|string|max:20',
            'home_mobile_3' => 'nullable|string|max:20',
            'reference_1' => 'nullable|string|max:255',
            'reference_2' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:worker_categories,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Handle file uploads
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('workers/photos', 'public');
            }
            if ($request->hasFile('nid_photo')) {
                $validated['nid_photo'] = $request->file('nid_photo')->store('workers/nid', 'public');
            }

            $worker->update($validated);

            DB::commit();

            return redirect()->route('admin.workers.show', $worker)
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified worker
     */
    public function destroy(Worker $worker)
    {
        $this->authorize('delete', $worker);

        $worker->delete();

        return redirect()->route('admin.workers.index')
            ->with('success', trans_common('deleted_successfully'));
    }

    /**
     * Generate unique worker ID
     */
    protected function generateWorkerId(): string
    {
        do {
            $workerId = 'WRK-' . strtoupper(Str::random(6));
        } while (Worker::where('worker_id', $workerId)->exists());

        return $workerId;
    }
}

