<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ActivityLogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of activity logs
     */
    public function index(Request $request)
    {
        $this->authorize('report.view');
        
        $query = Activity::with(['causer', 'subject'])
            ->latest();
        
        // Filter by log name
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }
        
        // Filter by causer (user)
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }
        
        // Filter by subject type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }
        
        // Filter by event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        
        $activities = $query->paginate(25);
        
        // Get filter options
        $logNames = Activity::distinct()->pluck('log_name');
        $subjectTypes = Activity::distinct()->pluck('subject_type')->filter();
        $events = Activity::distinct()->pluck('event')->filter();
        
        return view('admin.activity-logs.index', compact('activities', 'logNames', 'subjectTypes', 'events'));
    }

    /**
     * Display the specified activity log
     */
    public function show(Activity $activityLog)
    {
        $this->authorize('report.view');
        
        $activityLog->load(['causer', 'subject']);
        
        return view('admin.activity-logs.show', compact('activityLog'));
    }

    /**
     * Delete old activity logs
     */
    public function clean()
    {
        $this->authorize('settings.edit');
        
        $days = config('activitylog.delete_records_older_than_days', 365);
        
        $deleted = Activity::where('created_at', '<', now()->subDays($days))->delete();
        
        return redirect()->route('admin.activity-logs.index')
            ->with('success', "Deleted {$deleted} old activity log records.");
    }
}
