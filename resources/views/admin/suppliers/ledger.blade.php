@extends('adminlte::page')

@section('title', trans_common('supplier_ledger'))

@section('content_header')
    <h1>{{ trans_common('supplier_ledger') }} - {{ $supplier->name }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('supplier_ledger') }} - {{ $supplier->name }}" theme="primary" icon="fas fa-book">
        <form method="GET" action="{{ route('admin.suppliers.ledger', $supplier) }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label>{{ trans_common('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-4">
                    <label>{{ trans_common('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-4">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> {{ trans_common('filter') }}
                    </button>
                </div>
            </div>
        </form>

        <div class="row mb-3">
            <div class="col-md-4">
                <x-adminlte-info-box title="{{ trans_common('opening_balance') }}" 
                                     text="{{ currency_format($openingBalance) }}" 
                                     icon="fas fa-wallet" 
                                     theme="info"/>
            </div>
            <div class="col-md-4">
                <x-adminlte-info-box title="{{ trans_common('total_purchase') }}" 
                                     text="{{ currency_format($purchases->sum('total_amount')) }}" 
                                     icon="fas fa-shopping-cart" 
                                     theme="warning"/>
            </div>
            <div class="col-md-4">
                <x-adminlte-info-box title="{{ trans_common('total_payment') }}" 
                                     text="{{ currency_format($payments->sum('amount')) }}" 
                                     icon="fas fa-money-bill-wave" 
                                     theme="success"/>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{ trans_common('date') }}</th>
                        <th>{{ trans_common('type') }}</th>
                        <th>{{ trans_common('reference') }}</th>
                        <th>{{ trans_common('debit') }}</th>
                        <th>{{ trans_common('credit') }}</th>
                        <th>{{ trans_common('balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $balance = $openingBalance;
                    @endphp
                    <tr>
                        <td>{{ $dateFrom ?? '-' }}</td>
                        <td><strong>{{ trans_common('opening_balance') }}</strong></td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td><strong>{{ currency_format($balance) }}</strong></td>
                    </tr>
                    @foreach($purchases as $purchase)
                        @php
                            $balance += $purchase->total_amount;
                        @endphp
                        <tr>
                            <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                            <td>{{ trans_common('purchase') }}</td>
                            <td>{{ $purchase->purchase_number }}</td>
                            <td>{{ currency_format($purchase->total_amount) }}</td>
                            <td>-</td>
                            <td>{{ currency_format($balance) }}</td>
                        </tr>
                    @endforeach
                    @foreach($payments as $payment)
                        @php
                            $balance -= $payment->amount;
                        @endphp
                        <tr>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td>{{ trans_common('payment') }}</td>
                            <td>{{ $payment->payment_number ?? $payment->id }}</td>
                            <td>-</td>
                            <td>{{ currency_format($payment->amount) }}</td>
                            <td>{{ currency_format($balance) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right">{{ trans_common('closing_balance') }}</th>
                        <th>{{ currency_format($balance) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
