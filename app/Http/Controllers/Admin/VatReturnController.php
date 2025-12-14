<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VatReturn;
use App\Enums\VatReturnStatus;
use App\Rules\EnumRule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VatReturnController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show']);
        $this->middleware('permission:accounting.create')->only(['create', 'store']);
        $this->middleware('permission:accounting.edit')->only(['edit', 'update']);
        $this->middleware('permission:accounting.delete')->only(['destroy']);
    }

    /**
     * Display a listing of VAT returns
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $query = VatReturn::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('return_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('return_date', '<=', $request->date_to);
        }

        $vatReturns = $query->latest('return_date')->paginate(15);

        return view('admin.vat-returns.index', compact('vatReturns'));
    }

    /**
     * Show the form for creating a new VAT return
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('accounting.create'), 403);

        return view('admin.vat-returns.create');
    }

    /**
     * Store a newly created VAT return
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('accounting.create'), 403);

        $validated = $request->validate([
            'return_number' => 'nullable|string|max:50|unique:vat_returns,return_number',
            'return_date' => 'required|date',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'tailoring_vat' => 'nullable|numeric|min:0',
            'pos_sale_vat' => 'nullable|numeric|min:0',
            'sherwani_rent_vat' => 'nullable|numeric|min:0',
            'total_output_vat' => 'nullable|numeric|min:0',
            'total_input_vat' => 'nullable|numeric|min:0',
            'vat_payable' => 'nullable|numeric',
            'status' => ['required', new EnumRule(VatReturnStatus::class, 'Status')],
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate return number if not provided
            if (empty($validated['return_number'])) {
                do {
                    $returnNumber = 'VAT' . date('Ymd') . strtoupper(Str::random(4));
                } while (VatReturn::where('return_number', $returnNumber)->exists());
                $validated['return_number'] = $returnNumber;
            }

            // Calculate VAT payable if not provided
            if (!isset($validated['vat_payable'])) {
                $validated['vat_payable'] = ($validated['total_output_vat'] ?? 0) - ($validated['total_input_vat'] ?? 0);
            }

            $validated['user_id'] = auth()->id();
            VatReturn::create($validated);

            DB::commit();

            return redirect()->route('admin.vat-returns.index')
                ->with('success', trans_common('created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified VAT return
     */
    public function show(VatReturn $vatReturn)
    {
        abort_unless(auth()->user()?->can('accounting.view'), 403);

        $vatReturn->load('user');

        return view('admin.vat-returns.show', compact('vatReturn'));
    }

    /**
     * Show the form for editing the specified VAT return
     */
    public function edit(VatReturn $vatReturn)
    {
        abort_unless(auth()->user()?->can('accounting.edit'), 403);

        return view('admin.vat-returns.edit', compact('vatReturn'));
    }

    /**
     * Update the specified VAT return
     */
    public function update(Request $request, VatReturn $vatReturn)
    {
        abort_unless(auth()->user()?->can('accounting.edit'), 403);

        $validated = $request->validate([
            'return_number' => 'required|string|max:50|unique:vat_returns,return_number,' . $vatReturn->id,
            'return_date' => 'required|date',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'tailoring_vat' => 'nullable|numeric|min:0',
            'pos_sale_vat' => 'nullable|numeric|min:0',
            'sherwani_rent_vat' => 'nullable|numeric|min:0',
            'total_output_vat' => 'nullable|numeric|min:0',
            'total_input_vat' => 'nullable|numeric|min:0',
            'vat_payable' => 'nullable|numeric',
            'status' => ['required', new EnumRule(VatReturnStatus::class, 'Status')],
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate VAT payable if not provided
            if (!isset($validated['vat_payable'])) {
                $validated['vat_payable'] = ($validated['total_output_vat'] ?? 0) - ($validated['total_input_vat'] ?? 0);
            }

            $vatReturn->update($validated);

            DB::commit();

            return redirect()->route('admin.vat-returns.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified VAT return
     */
    public function destroy(VatReturn $vatReturn)
    {
        abort_unless(auth()->user()?->can('accounting.delete'), 403);

        DB::beginTransaction();
        try {
            $vatReturn->delete();
            DB::commit();

            return redirect()->route('admin.vat-returns.index')
                ->with('success', trans_common('deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage());
        }
    }
}
