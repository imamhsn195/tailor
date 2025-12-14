@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('order'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('order') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('order') }} #{{ $order->order_number }}" theme="primary" icon="fas fa-shopping-cart">
        <div class="row mb-3">
            <div class="col-md-12 text-right">
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> {{ trans_common('print') }}
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('order_number') }}</th>
                        <td><code>{{ $order->order_number }}</code></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('customer') }}</th>
                        <td>
                            <div>{{ $order->customer->name }}</div>
                            <small class="text-muted">{{ $order->customer->mobile ?? '-' }} | {{ $order->customer->email ?? '-' }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('branch') }}</th>
                        <td>{{ $order->branch->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('order_date') }}</th>
                        <td>{{ $order->order_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('trial_date') }}</th>
                        <td>{{ $order->trial_date ? $order->trial_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('delivery_date') }}</th>
                        <td>{{ $order->delivery_date->format('Y-m-d') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('status') }}</th>
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
                    </tr>
                    <tr>
                        <th>{{ trans_common('tailor_amount') }}</th>
                        <td>{{ number_format($order->tailor_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('fabrics_amount') }}</th>
                        <td>{{ number_format($order->fabrics_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('design_charge') }}</th>
                        <td>{{ number_format($order->design_charge, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('embroidery_charge') }}</th>
                        <td>{{ number_format($order->embroidery_charge, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('discount') }}</th>
                        <td>{{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('total_amount') }}</th>
                        <td><strong>{{ number_format($order->total_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('net_payable') }}</th>
                        <td><strong class="text-primary">{{ number_format($order->net_payable, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('paid_amount') }}</th>
                        <td><span class="badge badge-success">{{ number_format($order->paid_amount, 2) }}</span></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('due_amount') }}</th>
                        <td>
                            @if($order->due_amount > 0)
                                <span class="badge badge-warning">{{ number_format($order->due_amount, 2) }}</span>
                            @else
                                <span class="badge badge-success">{{ number_format($order->due_amount, 2) }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($order->items->count() > 0)
            <hr>
            <h5>{{ trans_common('order_items') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('product') }}</th>
                            <th>{{ trans_common('quantity') }}</th>
                            <th>{{ trans_common('unit_price') }}</th>
                            <th>{{ trans_common('total') }}</th>
                            <th>{{ trans_common('notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->total_price, 2) }}</td>
                                <td>{{ $item->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">{{ trans_common('total') }}:</th>
                            <th>{{ number_format($order->items->sum('total_price'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        @if($order->fabrics->count() > 0)
            <hr>
            <h5>{{ trans_common('fabrics') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('fabric') }}</th>
                            <th>{{ trans_common('quantity') }}</th>
                            <th>{{ trans_common('unit_price') }}</th>
                            <th>{{ trans_common('total') }}</th>
                            <th>{{ trans_common('is_in_house') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->fabrics as $fabric)
                            <tr>
                                <td>{{ $fabric->fabric_name }}</td>
                                <td>{{ number_format($fabric->quantity, 2) }}</td>
                                <td>{{ number_format($fabric->unit_price, 2) }}</td>
                                <td>{{ number_format($fabric->total_price, 2) }}</td>
                                <td>
                                    @if($fabric->is_in_house)
                                        <span class="badge badge-success">{{ trans_common('yes') }}</span>
                                    @else
                                        <span class="badge badge-info">{{ trans_common('no') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">{{ trans_common('total') }}:</th>
                            <th>{{ number_format($order->fabrics->sum('total_price'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        @if($order->notes)
            <hr>
            <h5>{{ trans_common('notes') }}</h5>
            <p>{{ $order->notes }}</p>
        @endif

        @if($order->measurements->count() > 0)
            <hr>
            <h5>{{ trans_common('measurement') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('measurement_template') }}</th>
                            <th>{{ trans_common('value') }}</th>
                            <th>{{ trans_common('date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->measurements as $measurement)
                            <tr>
                                <td>{{ $measurement->template->name ?? '-' }}</td>
                                <td>{{ $measurement->value ?? '-' }}</td>
                                <td>{{ $measurement->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($order->cuttings->count() > 0)
            <hr>
            <h5>{{ trans_common('cutting') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('cutting_master') }}</th>
                            <th>{{ trans_common('cutting_date') }}</th>
                            <th>{{ trans_common('status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->cuttings as $cutting)
                            <tr>
                                <td>{{ $cutting->cuttingMaster->name ?? '-' }}</td>
                                <td>{{ $cutting->cutting_date ? $cutting->cutting_date->format('Y-m-d') : '-' }}</td>
                                <td>{{ $cutting->status ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($order->deliveries->count() > 0)
            <hr>
            <h5>{{ trans_common('delivery') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('delivery_date') }}</th>
                            <th>{{ trans_common('delivered_amount') }}</th>
                            <th>{{ trans_common('status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->deliveries as $delivery)
                            <tr>
                                <td>{{ $delivery->delivery_date ? $delivery->delivery_date->format('Y-m-d') : '-' }}</td>
                                <td>{{ number_format($delivery->delivered_amount ?? 0, 2) }}</td>
                                <td>{{ $delivery->status ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-3">
            @can('order.edit')
                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop

@section('css')
<style>
    @media print {
        .btn, .content-header, .main-header, .main-sidebar, .main-footer {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
        }
    }
</style>
@stop
