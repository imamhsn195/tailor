@extends('adminlte::page')

@section('title', trans_common('accounting_reports'))

@section('content_header')
    <h1>{{ trans_common('accounting_reports') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('accounting_reports') }}" theme="primary" icon="fas fa-calculator">
        <form method="GET" action="{{ route('admin.reports.accounting') }}" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label>{{ trans_common('branch') }}</label>
                    <select name="branch_id" class="form-control">
                        <option value="">{{ trans_common('all') }} {{ trans_common('branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>{{ trans_common('account') }}</label>
                    <select name="account_id" class="form-control">
                        <option value="">{{ trans_common('all') }} {{ trans_common('accounts') }}</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>{{ trans_common('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label>{{ trans_common('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> {{ trans_common('filter') }}
                    </button>
                </div>
            </div>
        </form>

        @if(isset($summary))
            <div class="row mb-3">
                <div class="col-md-4">
                    <x-adminlte-info-box title="{{ trans_common('total_transactions') }}" 
                                         text="{{ number_format($summary['total'] ?? 0) }}" 
                                         icon="fas fa-exchange-alt" 
                                         theme="info"/>
                </div>
                <div class="col-md-4">
                    <x-adminlte-info-box title="{{ trans_common('total_debit') }}" 
                                         text="{{ currency_format($summary['total_debit'] ?? 0) }}" 
                                         icon="fas fa-arrow-down" 
                                         theme="danger"/>
                </div>
                <div class="col-md-4">
                    <x-adminlte-info-box title="{{ trans_common('total_credit') }}" 
                                         text="{{ currency_format($summary['total_credit'] ?? 0) }}" 
                                         icon="fas fa-arrow-up" 
                                         theme="success"/>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('date') }}</th>
                        <th>{{ trans_common('account') }}</th>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('description') }}</th>
                        <th>{{ trans_common('debit') }}</th>
                        <th>{{ trans_common('credit') }}</th>
                        <th>{{ trans_common('balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgers as $ledger)
                        <tr>
                            <td>{{ $ledger->date->format('Y-m-d') }}</td>
                            <td>{{ $ledger->account->name ?? '-' }}</td>
                            <td>{{ $ledger->branch->name ?? '-' }}</td>
                            <td>{{ $ledger->description ?? '-' }}</td>
                            <td>{{ currency_format($ledger->debit ?? 0) }}</td>
                            <td>{{ currency_format($ledger->credit ?? 0) }}</td>
                            <td>{{ currency_format($ledger->balance ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $ledgers->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
