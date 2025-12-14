@extends('adminlte::page')

@section('title', 'Subscriptions')

@section('content_header')
    <h1>Subscriptions</h1>
@stop

@section('content')
    <x-adminlte-card title="Subscriptions" theme="primary" icon="fas fa-credit-card">
        @if(isset($stats))
            <div class="row mb-3">
                <div class="col-md-3">
                    <x-adminlte-info-box title="Total" 
                                         text="{{ number_format($stats['total'] ?? 0) }}" 
                                         icon="fas fa-list" 
                                         theme="info"/>
                </div>
                <div class="col-md-3">
                    <x-adminlte-info-box title="Active" 
                                         text="{{ number_format($stats['active'] ?? 0) }}" 
                                         icon="fas fa-check-circle" 
                                         theme="success"/>
                </div>
                <div class="col-md-3">
                    <x-adminlte-info-box title="Cancelled" 
                                         text="{{ number_format($stats['cancelled'] ?? 0) }}" 
                                         icon="fas fa-times-circle" 
                                         theme="danger"/>
                </div>
                <div class="col-md-3">
                    <x-adminlte-info-box title="Expired" 
                                         text="{{ number_format($stats['expired'] ?? 0) }}" 
                                         icon="fas fa-clock" 
                                         theme="warning"/>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tenant</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Started At</th>
                        <th>Expires At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->id }}</td>
                            <td>{{ $subscription->tenant->name ?? '-' }}</td>
                            <td>{{ $subscription->plan->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td>{{ $subscription->started_at ? $subscription->started_at->format('Y-m-d') : '-' }}</td>
                            <td>{{ $subscription->expires_at ? $subscription->expires_at->format('Y-m-d') : '-' }}</td>
                            <td>
                                <a href="{{ route('super-admin.subscriptions.show', $subscription) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No subscriptions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $subscriptions->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </x-adminlte-card>
@stop
