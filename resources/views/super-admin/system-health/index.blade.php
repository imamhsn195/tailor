@extends('adminlte::page')

@section('title', 'System Health')

@section('content_header')
    <h1>System Health Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Tenants</span>
                    <span class="info-box-number">{{ $stats['tenants']['total'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Tenants</span>
                    <span class="info-box-number">{{ $stats['tenants']['active'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Subscriptions</span>
                    <span class="info-box-number">{{ $stats['subscriptions']['active'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Expiring Soon</span>
                    <span class="info-box-number">{{ $stats['subscriptions']['expiring_soon'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Database Statistics</h3>
                </div>
                <div class="card-body">
                    @if(isset($stats['databases']['error']))
                        <div class="alert alert-danger">{{ $stats['databases']['error'] }}</div>
                    @else
                        <table class="table">
                            <tr>
                                <th>Landlord DB Size:</th>
                                <td>{{ number_format($stats['databases']['landlord_size_mb'] ?? 0, 2) }} MB</td>
                            </tr>
                            <tr>
                                <th>Tenant Databases:</th>
                                <td>{{ $stats['databases']['tenant_count'] ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>Total Tenant DB Size:</th>
                                <td>{{ number_format($stats['databases']['total_tenant_size_mb'] ?? 0, 2) }} MB</td>
                            </tr>
                            <tr>
                                <th>Total Size:</th>
                                <td><strong>{{ number_format($stats['databases']['total_size_mb'] ?? 0, 2) }} MB</strong></td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Storage Statistics</h3>
                </div>
                <div class="card-body">
                    @if(isset($stats['storage']['error']))
                        <div class="alert alert-danger">{{ $stats['storage']['error'] }}</div>
                    @else
                        <table class="table">
                            <tr>
                                <th>Total Size:</th>
                                <td>{{ number_format($stats['storage']['total_size_mb'] ?? 0, 2) }} MB</td>
                            </tr>
                            <tr>
                                <th>File Count:</th>
                                <td>{{ $stats['storage']['file_count'] ?? 0 }}</td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Tenants</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Domain</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTenants as $tenant)
                                <tr>
                                    <td>{{ $tenant->name }}</td>
                                    <td>{{ $tenant->domain }}</td>
                                    <td>
                                        <span class="badge badge-{{ $tenant->status === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Subscriptions</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Plan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSubscriptions as $subscription)
                                <tr>
                                    <td>{{ $subscription->tenant->name }}</td>
                                    <td>{{ $subscription->plan->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $subscription->status === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

