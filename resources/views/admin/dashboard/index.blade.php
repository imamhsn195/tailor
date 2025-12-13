@extends('adminlte::page')

@section('title', trans_common('dashboard'))

@section('content_header')
    <h1>{{ trans_common('dashboard') }}</h1>
@stop

@section('content')
    <div class="row">
        <!-- Total Orders -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_orders']) }}</h3>
                    <p>{{ trans_common('total_orders') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <a href="#" class="small-box-footer">
                    {{ trans_common('more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Sales -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ currency_format($stats['total_sales']) }}</h3>
                    <p>{{ trans_common('total_sales') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="#" class="small-box-footer">
                    {{ trans_common('more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['total_customers']) }}</h3>
                    <p>{{ trans_common('total_customers') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="#" class="small-box-footer">
                    {{ trans_common('more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['total_products']) }}</h3>
                    <p>{{ trans_common('total_products') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <a href="#" class="small-box-footer">
                    {{ trans_common('more_info') }} <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Orders -->
        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header border-transparent">
                    <h3 class="card-title">{{ trans_common('pending_orders') }}</h3>
                    <div class="card-tools">
                        <span class="badge badge-danger">{{ $stats['pending_orders'] }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>{{ trans_common('order_no') }}</th>
                                    <th>{{ trans_common('customer') }}</th>
                                    <th>{{ trans_common('delivery_date') }}</th>
                                    <th>{{ trans_common('status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center">{{ trans_common('no_data_available') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Sales -->
        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ trans_common('today_sales') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="text-center">{{ currency_format($stats['today_sales']) }}</h2>
                            <p class="text-center text-muted">{{ trans_common('total_sales_today') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

