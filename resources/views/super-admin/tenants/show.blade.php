@extends('adminlte::page')

@section('title', 'View Tenant')

@section('content_header')
    <h1>View Tenant</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $tenant->name }}" theme="primary" icon="fas fa-building">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Name</th>
                        <td>{{ $tenant->name }}</td>
                    </tr>
                    <tr>
                        <th>Domain</th>
                        <td>{{ $tenant->domain }}</td>
                    </tr>
                    <tr>
                        <th>Database Name</th>
                        <td><code>{{ $tenant->database_name }}</code></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge badge-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'danger') }}">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                @if(isset($dbStats))
                    <h5>Database Statistics</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Tables</th>
                            <td>{{ $dbStats['tables'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <th>Size</th>
                            <td>{{ number_format(($dbStats['size'] ?? 0) / 1024, 2) }} MB</td>
                        </tr>
                    </table>
                @endif
            </div>
        </div>

        @if($tenant->activeSubscription)
            <div class="mt-3">
                <h5>Active Subscription</h5>
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Plan</th>
                        <td>{{ $tenant->activeSubscription->plan->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge badge-{{ $tenant->activeSubscription->status === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($tenant->activeSubscription->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Expires At</th>
                        <td>{{ $tenant->activeSubscription->expires_at ? $tenant->activeSubscription->expires_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="mt-3">
            <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </x-adminlte-card>
@stop
