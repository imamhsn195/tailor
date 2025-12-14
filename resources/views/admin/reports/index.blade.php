@extends('adminlte::page')

@section('title', trans_common('reports'))

@section('content_header')
    <h1>{{ trans_common('reports') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('reports') }}" theme="primary" icon="fas fa-chart-bar">
        <div class="row">
            <div class="col-md-4">
                <x-adminlte-card title="{{ trans_common('order_reports') }}" theme="info" icon="fas fa-file-alt">
                    <p>{{ trans_common('view_detailed_order_reports') }}</p>
                    <a href="{{ route('admin.reports.orders') }}" class="btn btn-info btn-block">
                        <i class="fas fa-arrow-right"></i> {{ trans_common('view') }} {{ trans_common('order_reports') }}
                    </a>
                </x-adminlte-card>
            </div>
            
            <div class="col-md-4">
                <x-adminlte-card title="{{ trans_common('sales_reports') }}" theme="success" icon="fas fa-chart-line">
                    <p>{{ trans_common('view_detailed_sales_reports') }}</p>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-success btn-block">
                        <i class="fas fa-arrow-right"></i> {{ trans_common('view') }} {{ trans_common('sales_reports') }}
                    </a>
                </x-adminlte-card>
            </div>
            
            <div class="col-md-4">
                <x-adminlte-card title="{{ trans_common('inventory_reports') }}" theme="warning" icon="fas fa-clipboard">
                    <p>{{ trans_common('view_detailed_inventory_reports') }}</p>
                    <a href="{{ route('admin.reports.inventory') }}" class="btn btn-warning btn-block">
                        <i class="fas fa-arrow-right"></i> {{ trans_common('view') }} {{ trans_common('inventory_reports') }}
                    </a>
                </x-adminlte-card>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-4">
                <x-adminlte-card title="{{ trans_common('factory_reports') }}" theme="secondary" icon="fas fa-industry">
                    <p>{{ trans_common('view_detailed_factory_reports') }}</p>
                    <a href="{{ route('admin.reports.factory') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-right"></i> {{ trans_common('view') }} {{ trans_common('factory_reports') }}
                    </a>
                </x-adminlte-card>
            </div>
            
            <div class="col-md-4">
                <x-adminlte-card title="{{ trans_common('hr_reports') }}" theme="danger" icon="fas fa-user-tie">
                    <p>{{ trans_common('view_detailed_hr_reports') }}</p>
                    <a href="{{ route('admin.reports.hr') }}" class="btn btn-danger btn-block">
                        <i class="fas fa-arrow-right"></i> {{ trans_common('view') }} {{ trans_common('hr_reports') }}
                    </a>
                </x-adminlte-card>
            </div>
            
            <div class="col-md-4">
                <x-adminlte-card title="{{ trans_common('accounting_reports') }}" theme="primary" icon="fas fa-calculator">
                    <p>{{ trans_common('view_detailed_accounting_reports') }}</p>
                    <a href="{{ route('admin.reports.accounting') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-arrow-right"></i> {{ trans_common('view') }} {{ trans_common('accounting_reports') }}
                    </a>
                </x-adminlte-card>
            </div>
        </div>
    </x-adminlte-card>
@stop
