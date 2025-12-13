@extends('adminlte::page')

@section('title', 'Activity Log Details')

@section('content_header')
    <h1>Activity Log Details</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity #{{ $activityLog->id }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Description</th>
                            <td>{{ $activityLog->description }}</td>
                        </tr>
                        <tr>
                            <th>Event</th>
                            <td>
                                <span class="badge badge-{{ $activityLog->event === 'created' ? 'success' : ($activityLog->event === 'updated' ? 'info' : ($activityLog->event === 'deleted' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($activityLog->event ?? 'N/A') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Log Name</th>
                            <td>{{ $activityLog->log_name }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $activityLog->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Causer (User)</th>
                            <td>
                                @if($activityLog->causer)
                                    {{ $activityLog->causer->name }} ({{ $activityLog->causer->email }})
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Subject Type</th>
                            <td>{{ $activityLog->subject_type }}</td>
                        </tr>
                        <tr>
                            <th>Subject ID</th>
                            <td>{{ $activityLog->subject_id }}</td>
                        </tr>
                        <tr>
                            <th>Subject</th>
                            <td>
                                @if($activityLog->subject)
                                    {{ class_basename($activityLog->subject_type) }} #{{ $activityLog->subject_id }}
                                @else
                                    <span class="text-muted">Deleted or Not Found</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($activityLog->properties->isNotEmpty())
                <div class="row mt-3">
                    <div class="col-12">
                        <h4>Properties</h4>
                        <div class="card">
                            <div class="card-body">
                                <pre class="mb-0">{{ json_encode($activityLog->properties->toArray(), JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($activityLog->changes->isNotEmpty())
                <div class="row mt-3">
                    <div class="col-12">
                        <h4>Changes</h4>
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Attribute</th>
                                            <th>Old Value</th>
                                            <th>New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activityLog->changes as $attribute => $change)
                                            <tr>
                                                <td><strong>{{ $attribute }}</strong></td>
                                                <td>
                                                    @if(isset($change['old']))
                                                        <span class="text-danger">{{ is_array($change['old']) ? json_encode($change['old']) : $change['old'] }}</span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($change['new']))
                                                        <span class="text-success">{{ is_array($change['new']) ? json_encode($change['new']) : $change['new'] }}</span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop


