@extends('adminlte::page')

@section('title', trans_common('dashboard'))

@section('content_header')
    <h1>{{ trans_common('dashboard') }}</h1>
@stop

@section('content')
    <!-- Filters -->
    <x-adminlte-card title="{{ trans_common('filters') }}" theme="info" icon="fas fa-filter">
        <form method="GET" action="{{ route('admin.dashboard.index') }}" class="row">
            <div class="col-md-3">
                <label>{{ trans_common('branch') }}</label>
                <select name="branch_id" class="form-control">
                    <option value="">{{ trans_common('all') }} {{ trans_common('branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>{{ trans_common('date_from') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label>{{ trans_common('date_to') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-search"></i> {{ trans_common('filter') }}
                </button>
            </div>
        </form>
    </x-adminlte-card>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('total_collections') }}" 
                                 text="{{ currency_format($totalCollections) }}" 
                                 icon="fas fa-money-bill-wave" 
                                 theme="success"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('total_orders') }}" 
                                 text="{{ number_format($totalOrders) }}" 
                                 icon="fas fa-shopping-cart" 
                                 theme="info"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('total_sales') }}" 
                                 text="{{ number_format($totalSales) }}" 
                                 icon="fas fa-cash-register" 
                                 theme="warning"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('total_expenses') }}" 
                                 text="{{ currency_format($totalExpenses) }}" 
                                 icon="fas fa-credit-card" 
                                 theme="danger"/>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('pending_orders') }}" 
                                 text="{{ number_format($pendingOrders) }}" 
                                 icon="fas fa-clock" 
                                 theme="warning"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('customers') }}" 
                                 text="{{ number_format($totalCustomers) }}" 
                                 icon="fas fa-users" 
                                 theme="primary"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('employees') }}" 
                                 text="{{ number_format($totalEmployees) }}" 
                                 icon="fas fa-user-tie" 
                                 theme="info"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('active_productions') }}" 
                                 text="{{ number_format($activeProductions) }}" 
                                 icon="fas fa-industry" 
                                 theme="success"/>
        </div>
    </div>

    <!-- Branch-wise Collections -->
    <x-adminlte-card title="{{ trans_common('branch_wise_collections') }}" theme="primary" icon="fas fa-chart-bar">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('order_collection') }}</th>
                        <th>{{ trans_common('pos_collection') }}</th>
                        <th>{{ trans_common('total_collection') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchCollections as $collection)
                        <tr>
                            <td>{{ $collection->name }}</td>
                            <td>{{ currency_format($collection->order_collection) }}</td>
                            <td>{{ currency_format($collection->pos_collection) }}</td>
                            <td><strong>{{ currency_format($collection->total_collection) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ trans_common('no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-adminlte-card>

    <!-- Branch-wise Orders -->
    <x-adminlte-card title="{{ trans_common('branch_wise_orders') }}" theme="info" icon="fas fa-shopping-bag">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('order_count') }}</th>
                        <th>{{ trans_common('total_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchOrders as $order)
                        <tr>
                            <td>{{ $order->name }}</td>
                            <td>{{ number_format($order->order_count) }}</td>
                            <td>{{ currency_format($order->order_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">{{ trans_common('no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-adminlte-card>

    <!-- Branch-wise Expenses -->
    <x-adminlte-card title="{{ trans_common('branch_wise_expenses') }}" theme="danger" icon="fas fa-chart-line">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('total_expense') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchExpenses as $expense)
                        <tr>
                            <td>{{ $expense->name }}</td>
                            <td>{{ currency_format($expense->total_expense) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">{{ trans_common('no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-adminlte-card>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-md-6">
            <x-adminlte-card title="{{ trans_common('recent_orders') }}" theme="primary" icon="fas fa-list">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ trans_common('order_number') }}</th>
                                <th>{{ trans_common('customer') }}</th>
                                <th>{{ trans_common('amount') }}</th>
                                <th>{{ trans_common('date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->customer->name ?? '-' }}</td>
                                    <td>{{ currency_format($order->net_payable) }}</td>
                                    <td>{{ $order->order_date->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ trans_common('no_records_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>

        <!-- Recent Sales -->
        <div class="col-md-6">
            <x-adminlte-card title="{{ trans_common('recent_sales') }}" theme="success" icon="fas fa-cash-register">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ trans_common('invoice_number') }}</th>
                                <th>{{ trans_common('customer') }}</th>
                                <th>{{ trans_common('amount') }}</th>
                                <th>{{ trans_common('date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                                <tr>
                                    <td>{{ $sale->invoice_number }}</td>
                                    <td>{{ $sale->customer_name ?? ($sale->customer->name ?? '-') }}</td>
                                    <td>{{ currency_format($sale->total_amount) }}</td>
                                    <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ trans_common('no_records_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if($lowStockProducts->count() > 0)
        <x-adminlte-card title="{{ trans_common('low_stock_alert') }}" theme="warning" icon="fas fa-exclamation-triangle">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>{{ trans_common('product') }}</th>
                            <th>{{ trans_common('branch') }}</th>
                            <th>{{ trans_common('quantity') }}</th>
                            <th>{{ trans_common('low_stock_alert') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->branch->name }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ number_format($item->product->low_stock_alert) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-adminlte-card>
    @endif
@stop
