@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('suppliers'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('suppliers') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $supplier->name }}" theme="primary" icon="fas fa-truck">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('name') }}</th>
                        <td>{{ $supplier->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('contact_person') }}</th>
                        <td>{{ $supplier->contact_person ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('mobile') }}</th>
                        <td>{{ $supplier->mobile ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('phone') }}</th>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('email') }}</th>
                        <td>{{ $supplier->email ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('address') }}</th>
                        <td>{{ $supplier->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('vat_no') }}</th>
                        <td>{{ $supplier->vat_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('discount_percentage') }}</th>
                        <td>{{ $supplier->discount_percentage }}%</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('total_purchase') }}</th>
                        <td>{{ currency_format($supplier->total_purchase_amount) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('total_paid') }}</th>
                        <td>{{ currency_format($supplier->total_paid_amount) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('total_due') }}</th>
                        <td><strong>{{ currency_format($supplier->total_due_amount) }}</strong></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('status') }}</th>
                        <td>
                            @if($supplier->is_active)
                                <span class="badge badge-success">{{ trans_common('active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            @can('supplier.edit')
                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            @can('supplier.view')
                <a href="{{ route('admin.suppliers.ledger', $supplier) }}" class="btn btn-warning">
                    <i class="fas fa-book"></i> {{ trans_common('ledger') }}
                </a>
            @endcan
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
