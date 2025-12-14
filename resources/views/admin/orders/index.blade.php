@extends('adminlte::page')

@section('title', trans_common('orders'))

@section('content_header')
    <h1>{{ trans_common('orders') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('orders') }}" theme="primary" icon="fas fa-shopping-cart">
        <x-search-form 
            :fields="[
                [
                    'name' => 'search',
                    'type' => 'text',
                    'placeholder' => 'Search by order number, customer name, mobile...',
                    'value' => request('search'),
                    'col' => 3
                ],
                [
                    'name' => 'status',
                    'type' => 'select',
                    'defaultOption' => ['value' => '', 'label' => trans_common('all') . ' ' . trans_common('status')],
                    'options' => [
                        'pending' => __('enums.order_status.pending'),
                        'in_progress' => __('enums.order_status.in_progress'),
                        'completed' => __('enums.order_status.completed'),
                        'delivered' => __('enums.order_status.delivered'),
                        'cancelled' => __('enums.order_status.cancelled')
                    ],
                    'value' => request('status'),
                    'col' => 2
                ],
                [
                    'name' => 'branch_id',
                    'type' => 'select',
                    'defaultOption' => ['value' => '', 'label' => trans_common('all') . ' ' . trans_common('branches')],
                    'options' => $branches->mapWithKeys(fn($branch) => [$branch->id => $branch->name])->toArray(),
                    'value' => request('branch_id'),
                    'col' => 2
                ],
                [
                    'name' => 'date_from',
                    'type' => 'date',
                    'placeholder' => 'From Date',
                    'value' => request('date_from'),
                    'col' => 2
                ],
                [
                    'name' => 'date_to',
                    'type' => 'date',
                    'placeholder' => 'To Date',
                    'value' => request('date_to'),
                    'col' => 2
                ]
            ]"
            :url="route('admin.orders.index')"
        />
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('order_number') }}</th>
                        <th>{{ trans_common('customer') }}</th>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('order_date') }}</th>
                        <th>{{ trans_common('delivery_date') }}</th>
                        <th>{{ trans_common('net_payable') }}</th>
                        <th>{{ trans_common('paid_amount') }}</th>
                        <th>{{ trans_common('due_amount') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><code>{{ $order->order_number }}</code></td>
                            <td>
                                <div>{{ $order->customer->name }}</div>
                                <small class="text-muted">{{ $order->customer->mobile ?? '-' }}</small>
                            </td>
                            <td>{{ $order->branch->name }}</td>
                            <td>{{ $order->order_date->format('Y-m-d') }}</td>
                            <td>{{ $order->delivery_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($order->net_payable, 2) }}</td>
                            <td>{{ number_format($order->paid_amount, 2) }}</td>
                            <td>
                                @if($order->due_amount > 0)
                                    <span class="badge badge-warning">{{ number_format($order->due_amount, 2) }}</span>
                                @else
                                    <span class="badge badge-success">{{ number_format($order->due_amount, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'in_progress' => 'primary',
                                        'completed' => 'info',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $statusColors[$order->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $color }}">
                                    {{ __('enums.order_status.' . $order->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('order.view')
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('order.edit')
                                        <a href="{{ route('admin.orders.edit', $order) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('order.delete')
                                        <form action="{{ route('admin.orders.destroy', $order) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('{{ trans_common('are_you_sure') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="{{ trans_common('delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
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

        @can('order.create')
            <div class="mt-3">
                <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('order') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop
