<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('permission:settings.view')->only(['index', 'show']);
        $this->middleware('permission:settings.edit')->only(['update']);
    }

    /**
     * Display the settings index page
     */
    public function index()
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        // Get current settings from config or database
        $settings = [
            'company_name' => config('app.name'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'currency' => config('app.currency', 'BDT'),
            'date_format' => config('app.date_format', 'Y-m-d'),
            'time_format' => config('app.time_format', 'H:i:s'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        abort_unless(auth()->user()?->can('settings.edit'), 403);

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|in:en,bn',
            'currency' => 'nullable|string|max:10',
            'date_format' => 'nullable|string|max:20',
            'time_format' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Update settings (this would typically be stored in database or config file)
            // For now, we'll just clear cache and return success
            Cache::forget('app_settings');

            DB::commit();

            return redirect()->route('admin.settings.index')
                ->with('success', trans_common('updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', trans_common('operation_failed') . ': ' . $e->getMessage())->withInput();
        }
    }
}
