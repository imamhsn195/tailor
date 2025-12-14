@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('products'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('products') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $product->name }}" theme="primary" icon="fas fa-box">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('name') }}</th>
                        <td>{{ $product->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('barcode') }}</th>
                        <td>
                            @if($product->barcode)
                                <code>{{ $product->barcode }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('qr_code') }}</th>
                        <td>
                            @if($product->qr_code)
                                <code>{{ $product->qr_code }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('category') }}</th>
                        <td>{{ $product->category ? $product->category->name : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('unit') }}</th>
                        <td>{{ $product->unit ? $product->unit->name . ' (' . $product->unit->abbreviation . ')' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('brand') }}</th>
                        <td>{{ $product->brand ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('purchase_price') }}</th>
                        <td>{{ number_format($product->purchase_price, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('sale_price') }}</th>
                        <td>{{ number_format($product->sale_price, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('fabric_width') }}</th>
                        <td>{{ $product->fabric_width ? number_format($product->fabric_width, 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('vat_percentage') }}</th>
                        <td>{{ $product->vat_percentage }}%</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('vat_type') }}</th>
                        <td>{{ ucfirst($product->vat_type) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('low_stock_alert') }}</th>
                        <td>{{ $product->low_stock_alert }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('status') }}</th>
                        <td>
                            @if($product->is_active)
                                <span class="badge badge-success">{{ trans_common('active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($product->description)
            <div class="mt-3">
                <h5>{{ trans_common('description') }}</h5>
                <p>{{ $product->description }}</p>
            </div>
        @endif

        @if($product->sizes->count() > 0)
            <div class="mt-3">
                <h5>{{ trans_common('sizes') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ trans_common('size') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->sizes as $size)
                                <tr>
                                    <td>{{ $size->size }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="mt-3">
            @can('product.edit')
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
