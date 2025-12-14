@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('discount'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('discount') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $discount->name }}" theme="primary" icon="fas fa-percent">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('name') }}</th>
                        <td>{{ $discount->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('type') }}</th>
                        <td>{{ $discount->type->label() }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('value') }}</th>
                        <td>
                            @if($discount->type->value === 'percentage')
                                {{ $discount->value }}%
                            @else
                                {{ number_format($discount->value, 2) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('applicable_to') }}</th>
                        <td>
                            @if($discount->customer)
                                {{ trans_common('customer') }}: {{ $discount->customer->name }}
                            @elseif($discount->membership)
                                {{ trans_common('membership') }}: {{ $discount->membership->name }}
                            @elseif($discount->product)
                                {{ trans_common('product') }}: {{ $discount->product->name }}
                            @else
                                {{ $discount->applicable_to ?? '-' }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('branch') }}</th>
                        <td>{{ $discount->branch ? $discount->branch->name : trans_common('all') . ' ' . trans_common('branches') }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('start_date') }}</th>
                        <td>{{ $discount->start_date ? $discount->start_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('end_date') }}</th>
                        <td>{{ $discount->end_date ? $discount->end_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('status') }}</th>
                        <td>
                            @if($discount->is_active)
                                <span class="badge badge-success">{{ trans_common('active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('created_at') }}</th>
                        <td>{{ $discount->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            @can('customer.edit')
                <a href="{{ route('admin.discounts.edit', $discount) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
