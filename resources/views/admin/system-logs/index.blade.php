@extends('adminlte::page')

@section('title', trans_common('system_logs'))

@section('content_header')
    <h1>{{ trans_common('system_logs') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('system_logs') }}" theme="primary" icon="fas fa-file-alt">
        @if(!$hasLogFiles)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> {{ trans_common('no_log_files_found') }}
            </div>
        @else
            @php
                $filterCount = 0;
                if (!empty($filters['channel'])) $filterCount++;
                if (!empty($filters['file'])) $filterCount++;
                if (!empty($filters['level'])) $filterCount++;
                if (!empty($filters['environment'])) $filterCount++;
                if (!empty($filters['date'])) $filterCount++;
                if (!empty($filters['search'])) $filterCount++;
            @endphp

            <x-table-header 
                :title="trans_common('system_logs')" 
                :filterCount="$filterCount"
            />

            <x-filter-panel :filterCount="$filterCount">
                <form id="filterForm" method="GET" action="{{ route('admin.system-logs.index') }}">
                    <div class="form-group">
                        <label>{{ trans_common('channel') }}</label>
                        <select name="channel" class="form-control">
                            <option value="">{{ trans_common('all_channels') }}</option>
                            @foreach($channels as $channel)
                                <option value="{{ $channel }}" {{ $filters['channel'] == $channel ? 'selected' : '' }}>
                                    {{ $channel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ trans_common('log_file') }}</label>
                        <select name="file" class="form-control">
                            <option value="">{{ trans_common('all_files') }}</option>
                            @foreach($files as $file)
                                <option value="{{ $file['name'] }}" {{ $filters['file'] == $file['name'] ? 'selected' : '' }}>
                                    {{ $file['name'] }} ({{ $file['size_human'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ trans_common('level') }}</label>
                        <select name="level" class="form-control">
                            <option value="">{{ trans_common('all_levels') }}</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" {{ $filters['level'] == $level ? 'selected' : '' }}>
                                    {{ strtoupper($level) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ trans_common('environment') }}</label>
                        <input type="text" name="environment" class="form-control" value="{{ $filters['environment'] ?? '' }}" placeholder="e.g., local, production">
                    </div>

                    <div class="form-group">
                        <label>{{ trans_common('entry_date') }}</label>
                        <input type="date" name="date" class="form-control" value="{{ $filters['date'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>{{ trans_common('search_text') }}</label>
                        <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ trans_common('search_text') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ trans_common('entries_to_display') }}</label>
                        <input type="number" name="per_page" class="form-control" value="{{ $filters['per_page'] ?? 50 }}" min="10" max="300">
                    </div>

                    <input type="hidden" name="max_files" value="{{ $filters['max_files'] ?? 3 }}">
                </form>
            </x-filter-panel>

            @if($filterCount > 0)
                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2">
                        @if(!empty($filters['channel']))
                            <span class="badge badge-info">
                                {{ trans_common('channel') }}: {{ $filters['channel'] }}
                                <a href="{{ route('admin.system-logs.index', array_merge(request()->except('channel'), ['channel' => ''])) }}" class="text-white ml-1">×</a>
                            </span>
                        @endif
                        @if(!empty($filters['file']))
                            <span class="badge badge-info">
                                {{ trans_common('log_file') }}: {{ $filters['file'] }}
                                <a href="{{ route('admin.system-logs.index', array_merge(request()->except('file'), ['file' => ''])) }}" class="text-white ml-1">×</a>
                            </span>
                        @endif
                        @if(!empty($filters['level']))
                            <span class="badge badge-info">
                                {{ trans_common('level') }}: {{ strtoupper($filters['level']) }}
                                <a href="{{ route('admin.system-logs.index', array_merge(request()->except('level'), ['level' => ''])) }}" class="text-white ml-1">×</a>
                            </span>
                        @endif
                        @if(!empty($filters['environment']))
                            <span class="badge badge-info">
                                {{ trans_common('environment') }}: {{ $filters['environment'] }}
                                <a href="{{ route('admin.system-logs.index', array_merge(request()->except('environment'), ['environment' => ''])) }}" class="text-white ml-1">×</a>
                            </span>
                        @endif
                        @if(!empty($filters['date']))
                            <span class="badge badge-info">
                                {{ trans_common('entry_date') }}: {{ $filters['date'] }}
                                <a href="{{ route('admin.system-logs.index', array_merge(request()->except('date'), ['date' => ''])) }}" class="text-white ml-1">×</a>
                            </span>
                        @endif
                        @if(!empty($filters['search']))
                            <span class="badge badge-info">
                                {{ trans_common('search') }}: {{ $filters['search'] }}
                                <a href="{{ route('admin.system-logs.index', array_merge(request()->except('search'), ['search' => ''])) }}" class="text-white ml-1">×</a>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                    @if($meta['files_scanned'] > 0)
                        <small class="text-muted">
                            {{ trans_common('showing_entries', [
                                'count' => $logs->count(),
                                'per_page' => $filters['per_page'],
                                'files' => $meta['files_scanned']
                            ]) }}
                        </small>
                    @endif
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-secondary" id="refreshBtn" title="{{ trans_common('refresh') }}">
                        <i class="fas fa-sync-alt"></i> {{ trans_common('refresh') }}
                    </button>
                    @can('system-log.delete')
                        <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn" disabled>
                            <i class="fas fa-trash"></i> {{ trans_common('delete_selected') }}
                        </button>
                    @endcan
                </div>
            </div>

            <div id="logsContainer">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllCheckbox">
                                </th>
                                <th>{{ trans_common('timestamp') }}</th>
                                <th>{{ trans_common('level') }}</th>
                                <th>{{ trans_common('channel_file') }}</th>
                                <th>{{ trans_common('message_context') }}</th>
                                @can('system-log.delete')
                                    <th width="80">{{ trans_common('actions') }}</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            @forelse($logs as $log)
                                <tr data-timestamp="{{ $log['timestamp']->toIso8601String() }}" data-file="{{ $log['file'] }}">
                                    <td>
                                        <input type="checkbox" class="log-entry-checkbox" 
                                               data-file="{{ $log['file'] }}" 
                                               data-timestamp="{{ $log['timestamp']->toIso8601String() }}">
                                    </td>
                                    <td>
                                        <small>{{ $log['timestamp']->format('M d, Y H:i:s') }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $levelColors = [
                                                'debug' => 'secondary',
                                                'info' => 'info',
                                                'notice' => 'primary',
                                                'warning' => 'warning',
                                                'error' => 'danger',
                                                'critical' => 'danger',
                                                'alert' => 'danger',
                                                'emergency' => 'danger',
                                            ];
                                            $color = $levelColors[$log['level']] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-{{ $color }}">{{ strtoupper($log['level']) }}</span>
                                    </td>
                                    <td>
                                        <small>
                                            <strong>{{ $log['channel'] }}</strong><br>
                                            <span class="text-muted">{{ $log['file'] }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ \Illuminate\Support\Str::limit($log['message'], 100) }}</strong>
                                        </div>
                                        <details class="mt-1">
                                            <summary class="text-primary cursor-pointer" style="cursor: pointer;">
                                                <small>{{ trans_common('expand_details') }}</small>
                                            </summary>
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <div class="mb-2">
                                                    <strong>{{ trans_common('message') }}:</strong>
                                                    <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">{{ $log['message'] }}</pre>
                                                </div>
                                                @if($log['context'])
                                                    <div>
                                                        <strong>{{ trans_common('context') }}:</strong>
                                                        <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">{{ json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                @endif
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>{{ trans_common('environment') }}:</strong> {{ $log['environment'] }}
                                                    </small>
                                                </div>
                                            </div>
                                        </details>
                                    </td>
                                    @can('system-log.delete')
                                        <td>
                                            <form action="{{ route('admin.system-logs.destroy') }}" method="POST" class="d-inline delete-log-form">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="file" value="{{ $log['file'] }}">
                                                <input type="hidden" name="timestamp" value="{{ $log['timestamp']->toIso8601String() }}">
                                                @foreach(request()->only(['channel', 'file', 'level', 'environment', 'date', 'search', 'per_page', 'max_files']) as $key => $value)
                                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                @endforeach
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        title="{{ trans_common('delete_log_entry') }}"
                                                        onclick="return confirm('{{ trans_common('delete_log_entry_confirm') }}');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->can('system-log.delete') ? '6' : '5' }}" class="text-center">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">{{ trans_common('no_log_entries_found') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-adminlte-card>
@stop

@section('css')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    #logsContainer {
        min-height: 200px;
    }
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const logEntryCheckboxes = document.querySelectorAll('.log-entry-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const logsContainer = document.getElementById('logsContainer');

    // Apply filters
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            filterForm.submit();
        });
    }

    // Clear filters
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            window.location.href = '{{ route("admin.system-logs.index") }}';
        });
    }

    // Refresh logs
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('_t', Date.now());
            window.location.href = url.toString();
        });
    }

    // Select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.log-entry-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }

    // Individual checkbox change
    logEntryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();
            if (selectAllCheckbox) {
                const allChecked = Array.from(document.querySelectorAll('.log-entry-checkbox')).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
        });
    });

    function updateBulkDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.log-entry-checkbox:checked');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = checkedBoxes.length === 0;
        }
    }

    // Bulk delete
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.log-entry-checkbox:checked');
            if (checkedBoxes.length === 0) {
                return;
            }

            const count = checkedBoxes.length;
            if (!confirm('{{ trans_common("delete_selected_logs_confirm") }}'.replace('{count}', count))) {
                return;
            }

            const entries = Array.from(checkedBoxes).map(checkbox => ({
                file: checkbox.dataset.file,
                timestamp: checkbox.dataset.timestamp
            }));

            // Show loading
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="sr-only">{{ trans_common("loading") }}</span></div>';
            logsContainer.style.position = 'relative';
            logsContainer.appendChild(overlay);

            // Send AJAX request
            fetch('{{ route("admin.system-logs.bulk-destroy") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    entries: entries,
                    @foreach(request()->only(['channel', 'file', 'level', 'environment', 'date', 'search', 'per_page', 'max_files']) as $key => $value)
                    '{{ $key }}': '{{ $value }}',
                    @endforeach
                })
            })
            .then(response => response.json())
            .then(data => {
                overlay.remove();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || '{{ trans_common("unable_to_delete_log_entries") }}');
                }
            })
            .catch(error => {
                overlay.remove();
                console.error('Error:', error);
                alert('{{ trans_common("unable_to_delete_log_entries") }}');
            });
        });
    }

    // Initial update
    updateBulkDeleteButton();
});
</script>
@stop
