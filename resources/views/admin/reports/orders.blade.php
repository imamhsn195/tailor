@extends('adminlte::page')

@section('title', trans_common('order_reports'))

@section('content_header')
    <h1>{{ trans_common('order_reports') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('order_reports') }}" theme="primary" icon="fas fa-file-alt">
        <form method="GET" action="{{ route('admin.reports.orders') }}" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label>{{ trans_common('branch') }}</label>
                    <select name="branch_id" class="form-control">
                        <option value="">{{ trans_common('all') }} {{ trans_common('branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>{{ trans_common('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label>{{ trans_common('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> {{ trans_common('filter') }}
                    </button>
                </div>
            </div>
        </form>

        @if(isset($summary))
            <div class="row mb-3">
                <div class="col-md-3">
                    <x-adminlte-info-box title="{{ trans_common('total_orders') }}" 
                                         text="{{ number_format($summary['total'] ?? 0) }}" 
                                         icon="fas fa-shopping-cart" 
                                         theme="info"/>
                </div>
                <div class="col-md-3">
                    <x-adminlte-info-box title="{{ trans_common('total_amount') }}" 
                                         text="{{ currency_format($summary['total_amount'] ?? 0) }}" 
                                         icon="fas fa-money-bill-wave" 
                                         theme="success"/>
                </div>
                <div class="col-md-3">
                    <x-adminlte-info-box title="{{ trans_common('pending') }}" 
                                         text="{{ number_format($summary['pending'] ?? 0) }}" 
                                         icon="fas fa-clock" 
                                         theme="warning"/>
                </div>
                <div class="col-md-3">
                    <x-adminlte-info-box title="{{ trans_common('completed') }}" 
                                         text="{{ number_format($summary['completed'] ?? 0) }}" 
                                         icon="fas fa-check-circle" 
                                         theme="success"/>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('order_number') }}</th>
                        <th>{{ trans_common('customer') }}</th>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('order_date') }}</th>
                        <th>{{ trans_common('total_amount') }}</th>
                        <th>{{ trans_common('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ $order->branch->name ?? '-' }}</td>
                            <td>{{ $order->order_date->format('Y-m-d') }}</td>
                            <td>{{ currency_format($order->net_payable) }}</td>
                            <td>
                                <span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'info') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
