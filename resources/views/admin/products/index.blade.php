@extends('adminlte::page')

@section('title', trans_common('products'))

@section('content_header')
    <h1>{{ trans_common('products') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('products') }}" theme="primary" icon="fas fa-box">
        @include('components.search-form', ['route' => route('admin.products.index')])
        
        <div class="row mb-3">
            <div class="col-md-3">
                <select name="category_id" class="form-control" onchange="this.form.submit()">
                    <option value="">{{ trans_common('all') }} {{ trans_common('categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">{{ trans_common('all') }} {{ trans_common('status') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ trans_common('active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ trans_common('inactive') }}</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('id') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('barcode') }}</th>
                        <th>{{ trans_common('category') }}</th>
                        <th>{{ trans_common('sale_price') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->barcode ?? '-' }}</td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ number_format($product->sale_price, 2) }}</td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">{{ trans_common('active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('product.view')
                                        <a href="{{ route('admin.products.show', $product) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('product.edit')
                                        <a href="{{ route('admin.products.edit', $product) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('product.delete')
                                        <form action="{{ route('admin.products.destroy', $product) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('{{ trans_common('are_you_sure') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="{{ trans_common('delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
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
            {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('product.create')
            <div class="mt-3">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('products') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop

