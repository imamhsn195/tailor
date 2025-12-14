@extends('adminlte::page')

@section('title', trans_common('suppliers'))

@section('content_header')
    <h1>{{ trans_common('suppliers') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('suppliers') }}" theme="primary" icon="fas fa-truck">
        @include('components.search-form', ['route' => route('admin.suppliers.index')])
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('id') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('contact_person') }}</th>
                        <th>{{ trans_common('mobile') }}</th>
                        <th>{{ trans_common('email') }}</th>
                        <th>{{ trans_common('total_due') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->id }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person ?? '-' }}</td>
                            <td>{{ $supplier->mobile ?? '-' }}</td>
                            <td>{{ $supplier->email ?? '-' }}</td>
                            <td>{{ currency_format($supplier->total_due_amount) }}</td>
                            <td>
                                @if($supplier->is_active)
                                    <span class="badge badge-success">{{ trans_common('active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('supplier.view')
                                        <a href="{{ route('admin.suppliers.show', $supplier) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('supplier.edit')
                                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('supplier.view')
                                        <a href="{{ route('admin.suppliers.ledger', $supplier) }}" 
                                           class="btn btn-sm btn-warning" 
                                           title="{{ trans_common('ledger') }}">
                                            <i class="fas fa-book"></i>
                                        </a>
                                    @endcan
                                    @can('supplier.delete')
                                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" 
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
                            <td colspan="8" class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $suppliers->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('supplier.create')
            <div class="mt-3">
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('suppliers') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop
