@extends('adminlte::page')

@section('title', 'View Subscription')

@section('content_header')
    <h1>View Subscription</h1>
@stop

@section('content')
    <x-adminlte-card title="Subscription Details" theme="primary" icon="fas fa-credit-card">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">ID</th>
                        <td>{{ $subscription->id }}</td>
                    </tr>
                    <tr>
                        <th>Tenant</th>
                        <td>{{ $subscription->tenant->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Plan</th>
                        <td>{{ $subscription->plan->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge badge-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Started At</th>
                        <td>{{ $subscription->started_at ? $subscription->started_at->format('Y-m-d H:i:s') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Expires At</th>
                        <td>{{ $subscription->expires_at ? $subscription->expires_at->format('Y-m-d H:i:s') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td>{{ $subscription->plan->formatted_price ?? currency_format(0, config('app.currency', 'BDT')) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </x-adminlte-card>
@stop
