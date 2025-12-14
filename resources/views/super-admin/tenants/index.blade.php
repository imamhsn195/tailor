@extends('adminlte::page')

@section('title', 'Tenants Management')

@section('content_header')
    <h1>Tenants Management</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Tenants</span>
                    <span class="info-box-number">{{ $stats['total'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active</span>
                    <span class="info-box-number">{{ $stats['active'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-pause-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Suspended</span>
                    <span class="info-box-number">{{ $stats['suspended'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Inactive</span>
                    <span class="info-box-number">{{ $stats['inactive'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Tenants</h3>
            <div class="card-tools">
                <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create Tenant
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>Database</th>
                        <th>Status</th>
                        <th>Subscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->id }}</td>
                            <td>{{ $tenant->name }}</td>
                            <td>{{ $tenant->domain }}</td>
                            <td><code>{{ $tenant->database_name }}</code></td>
                            <td>
                                <span class="badge badge-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($tenant->status) }}
                                </span>
                            </td>
                            <td>
                                @if($tenant->activeSubscription)
                                    <span class="badge badge-info">{{ $tenant->activeSubscription->plan->name ?? 'N/A' }}</span>
                                @else
                                    <span class="text-muted">No subscription</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No tenants found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $tenants->links() }}
        </div>
    </div>
@stop

