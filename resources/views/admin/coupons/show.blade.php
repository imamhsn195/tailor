@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('coupon'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('coupon') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $coupon->name }}" theme="primary" icon="fas fa-ticket-alt">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('coupon_code') }}</th>
                        <td><code>{{ $coupon->code }}</code></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('name') }}</th>
                        <td>{{ $coupon->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('type') }}</th>
                        <td>{{ $coupon->type->label() }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('value') }}</th>
                        <td>
                            @if($coupon->type->value === 'percentage')
                                {{ $coupon->value }}%
                            @else
                                {{ number_format($coupon->value, 2) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('minimum_amount') }}</th>
                        <td>{{ $coupon->minimum_amount ? number_format($coupon->minimum_amount, 2) : '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('usage_limit') }}</th>
                        <td>{{ $coupon->usage_limit ?? trans_common('unlimited') }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('used_count') }}</th>
                        <td>{{ $coupon->used_count }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('start_date') }}</th>
                        <td>{{ $coupon->start_date ? $coupon->start_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('end_date') }}</th>
                        <td>{{ $coupon->end_date ? $coupon->end_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('status') }}</th>
                        <td>
                            @if($coupon->is_active)
                                <span class="badge badge-success">{{ trans_common('active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('valid') }}</th>
                        <td>
                            @if($coupon->isValid())
                                <span class="badge badge-success">{{ trans_common('yes') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('no') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            @can('customer.edit')
                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
