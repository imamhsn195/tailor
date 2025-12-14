@extends('adminlte::page')

@section('title', trans_common('inventory'))

@section('content_header')
    <h1>{{ trans_common('inventory') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('inventory') }}" theme="primary" icon="fas fa-boxes">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="mb-3">
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
                    <label>{{ trans_common('product') }}</label>
                    <select name="product_id" class="form-control">
                        <option value="">{{ trans_common('all') }} {{ trans_common('products') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
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
                        <th>{{ trans_common('product') }}</th>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('quantity') }}</th>
                        <th>{{ trans_common('unit_price') }}</th>
                        <th>{{ trans_common('total_value') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $inventory)
                        <tr>
                            <td>{{ $inventory->product->name ?? '-' }}</td>
                            <td>{{ $inventory->branch->name ?? '-' }}</td>
                            <td>{{ number_format($inventory->quantity, 2) }}</td>
                            <td>{{ currency_format($inventory->product->purchase_price ?? 0) }}</td>
                            <td>{{ currency_format(($inventory->quantity ?? 0) * ($inventory->product->purchase_price ?? 0)) }}</td>
                            <td>
                                <a href="{{ route('admin.inventory.show', $inventory) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="{{ trans_common('view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $inventories->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.inventory.stock-in') }}" class="btn btn-success">
                <i class="fas fa-arrow-down"></i> {{ trans_common('stock_in') }}
            </a>
            <a href="{{ route('admin.inventory.stock-out') }}" class="btn btn-warning">
                <i class="fas fa-arrow-up"></i> {{ trans_common('stock_out') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
