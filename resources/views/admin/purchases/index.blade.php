@extends('adminlte::page')

@section('title', trans_common('purchases'))

@section('content_header')
    <h1>{{ trans_common('purchases') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('purchases') }}" theme="primary" icon="fas fa-shopping-cart">
        <form method="GET" action="{{ route('admin.purchases.index') }}" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label>{{ trans_common('supplier') }}</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">{{ trans_common('all') }} {{ trans_common('suppliers') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
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
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> {{ trans_common('filter') }}
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('purchase_number') }}</th>
                        <th>{{ trans_common('supplier') }}</th>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('purchase_date') }}</th>
                        <th>{{ trans_common('total_amount') }}</th>
                        <th>{{ trans_common('paid_amount') }}</th>
                        <th>{{ trans_common('due_amount') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_number }}</td>
                            <td>{{ $purchase->supplier->name ?? '-' }}</td>
                            <td>{{ $purchase->branch->name ?? '-' }}</td>
                            <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                            <td>{{ currency_format($purchase->total_amount) }}</td>
                            <td>{{ currency_format($purchase->paid_amount) }}</td>
                            <td>{{ currency_format($purchase->due_amount) }}</td>
                            <td>
                                <span class="badge badge-{{ $purchase->status === 'completed' ? 'success' : ($purchase->status === 'pending' ? 'warning' : 'info') }}">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.purchases.show', $purchase) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="{{ trans_common('view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.purchases.edit', $purchase) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="{{ trans_common('edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $purchases->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('purchases') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
