@extends('adminlte::page')

@section('title', trans_common('deliveries'))

@section('content_header')
    <h1>{{ trans_common('deliveries') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('deliveries') }}" theme="primary" icon="fas fa-truck-loading">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('delivery_number') }}</th>
                        <th>{{ trans_common('order') }}</th>
                        <th>{{ trans_common('customer') }}</th>
                        <th>{{ trans_common('delivery_date') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->delivery_number ?? $delivery->id }}</td>
                            <td>{{ $delivery->order->order_number ?? '-' }}</td>
                            <td>{{ $delivery->order->customer->name ?? '-' }}</td>
                            <td>{{ $delivery->delivery_date ? $delivery->delivery_date->format('Y-m-d') : '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $delivery->status === 'delivered' ? 'success' : 'warning' }}">
                                    {{ ucfirst($delivery->status ?? 'pending') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.deliveries.show', $delivery) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="{{ trans_common('view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
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
            {{ $deliveries->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.deliveries.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('deliveries') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
