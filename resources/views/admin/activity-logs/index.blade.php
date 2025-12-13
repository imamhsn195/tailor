@extends('adminlte::page')

@section('title', 'Activity Logs')

@section('content_header')
    <h1>Activity Logs</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Activity Logs</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Log Name</label>
                            <select name="log_name" class="form-control">
                                <option value="">All</option>
                                @foreach($logNames as $logName)
                                    <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                        {{ $logName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Event</label>
                            <select name="event" class="form-control">
                                <option value="">All</option>
                                @foreach($events as $event)
                                    <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                        {{ ucfirst($event) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search in description..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Logs</h3>
            <div class="card-tools">
                <form method="POST" action="{{ route('admin.activity-logs.clean') }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete old activity logs?')">
                        Clean Old Logs
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Subject</th>
                        <th>Causer</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->id }}</td>
                            <td>{{ $activity->description }}</td>
                            <td>
                                @if($activity->subject)
                                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($activity->causer)
                                    {{ $activity->causer->name }}
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $activity->event === 'created' ? 'success' : ($activity->event === 'updated' ? 'info' : ($activity->event === 'deleted' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($activity->event ?? 'N/A') }}
                                </span>
                            </td>
                            <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <a href="{{ route('admin.activity-logs.show', $activity) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No activity logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $activities->links() }}
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

