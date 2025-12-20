<?php

namespace App\Http\Controllers;

use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    public function __construct(private SystemLogService $logService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        abort_unless(auth()->user()?->can('system-log.view'), 403);

        $filters = [
            'channel' => $request->input('channel'),
            'file' => $request->input('file'),
            'level' => $request->input('level'),
            'environment' => $request->input('environment'),
            'date' => $request->input('date'),
            'search' => $request->input('search'),
            'max_files' => $request->integer('max_files', 3),
        ];

        $filters = array_map(fn ($value) => $value === '' ? null : $value, $filters);

        $perPage = (int) $request->integer('per_page', 50);
        $perPage = max(10, min($perPage, 300));

        $logResult = $this->logService->getEntries($filters, $perPage);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $logResult['entries']->map(function (array $entry) {
                    return [
                        'timestamp' => $entry['timestamp']->toIso8601String(),
                        'timestamp_human' => $entry['timestamp']->format('M d, Y H:i:s'),
                        'environment' => $entry['environment'],
                        'level' => $entry['level'],
                        'message' => $entry['message'],
                        'context' => $entry['context'],
                        'channel' => $entry['channel'],
                        'file' => $entry['file'],
                    ];
                }),
                'meta' => array_merge($logResult['meta'], [
                    'per_page' => $perPage,
                    'available_levels' => $this->logService->availableLevels(),
                ]),
            ]);
        }

        $allFiles = $this->logService->listFiles();
        $channels = $this->logService->availableChannels();

        return view('admin.system-logs.index', [
            'filters' => array_merge([
                'channel' => null,
                'file' => null,
                'level' => null,
                'environment' => null,
                'date' => null,
                'search' => null,
                'per_page' => $perPage,
                'max_files' => $filters['max_files'] ?? 3,
            ], $filters),
            'logs' => $logResult['entries'],
            'meta' => $logResult['meta'],
            'channels' => $channels,
            'files' => $allFiles,
            'levels' => $this->logService->availableLevels(),
            'hasLogFiles' => $allFiles->isNotEmpty(),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('system-log.delete'), 403);

        $request->validate([
            'file' => 'required|string',
            'timestamp' => 'required|string',
        ]);

        $file = $request->input('file');
        $timestamp = $request->input('timestamp');

        $deleted = $this->logService->deleteEntry($file, $timestamp);

        if ($deleted) {
            return redirect()->route('admin.system-logs.index', $request->only(['channel', 'file', 'level', 'environment', 'date', 'search', 'per_page', 'max_files']))
                ->with('success', __('common.log_entry_deleted_successfully'));
        }

        return redirect()->route('admin.system-logs.index', $request->only(['channel', 'file', 'level', 'environment', 'date', 'search', 'per_page', 'max_files']))
            ->with('error', __('common.unable_to_delete_log_entry'));
    }

    public function bulkDestroy(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless(auth()->user()?->can('system-log.delete'), 403);

        if ($request->isJson() || $request->wantsJson()) {
            $data = $request->json()->all();
            $request->merge($data);
        }

        $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.file' => 'required|string',
            'entries.*.timestamp' => 'required|string',
        ]);

        $entries = $request->input('entries', []);
        $deletedCount = 0;
        $failedCount = 0;

        foreach ($entries as $entry) {
            $deleted = $this->logService->deleteEntry($entry['file'], $entry['timestamp']);
            if ($deleted) {
                $deletedCount++;
            } else {
                $failedCount++;
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            if ($deletedCount > 0 && $failedCount === 0) {
                return response()->json([
                    'success' => true,
                    'message' => __('common.log_entries_deleted_successfully', ['count' => $deletedCount]),
                    'deleted_count' => $deletedCount,
                ]);
            } elseif ($deletedCount > 0 && $failedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => __('common.some_log_entries_deleted', ['deleted' => $deletedCount, 'failed' => $failedCount]),
                    'deleted_count' => $deletedCount,
                    'failed_count' => $failedCount,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('common.unable_to_delete_log_entries'),
                ], 400);
            }
        }

        $redirect = redirect()->route('admin.system-logs.index', $request->only(['channel', 'file', 'level', 'environment', 'date', 'search', 'per_page', 'max_files']));

        if ($deletedCount > 0 && $failedCount === 0) {
            return $redirect->with('success', __('common.log_entries_deleted_successfully', ['count' => $deletedCount]));
        } elseif ($deletedCount > 0 && $failedCount > 0) {
            return $redirect->with('warning', __('common.some_log_entries_deleted', ['deleted' => $deletedCount, 'failed' => $failedCount]));
        } else {
            return $redirect->with('error', __('common.unable_to_delete_log_entries'));
        }
    }
}


