@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('gift_voucher'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('gift_voucher') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $giftVoucher->name ?? $giftVoucher->voucher_code }}" theme="primary" icon="fas fa-gift">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('voucher_code') }}</th>
                        <td><code>{{ $giftVoucher->voucher_code }}</code></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('name') }}</th>
                        <td>{{ $giftVoucher->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('amount') }}</th>
                        <td>{{ number_format($giftVoucher->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('customer') }}</th>
                        <td>{{ $giftVoucher->customer ? $giftVoucher->customer->name : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('issued_date') }}</th>
                        <td>{{ $giftVoucher->issued_date->format('Y-m-d') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('expiry_date') }}</th>
                        <td>{{ $giftVoucher->expiry_date ? $giftVoucher->expiry_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('used_date') }}</th>
                        <td>{{ $giftVoucher->used_date ? $giftVoucher->used_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('status') }}</th>
                        <td>
                            <span class="badge badge-{{ $giftVoucher->status->badgeColor() }}">
                                {{ $giftVoucher->status->label() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('valid') }}</th>
                        <td>
                            @if($giftVoucher->isValid())
                                <span class="badge badge-success">{{ trans_common('yes') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('no') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('notes') }}</th>
                        <td>{{ $giftVoucher->notes ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('created_at') }}</th>
                        <td>{{ $giftVoucher->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            @can('customer.edit')
                <a href="{{ route('admin.gift-vouchers.edit', $giftVoucher) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.gift-vouchers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
