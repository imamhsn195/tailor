@extends('adminlte::page')

@section('title', trans_common('companies'))

@section('content_header')
    <h1>{{ trans_common('companies') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('companies') }}" theme="primary" icon="fas fa-building">
        @include('components.search-form', ['route' => route('admin.companies.index')])
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('id') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('email') }}</th>
                        <th>{{ trans_common('phone') }}</th>
                        <th>{{ trans_common('branches') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td>{{ $company->id }}</td>
                            <td>{{ $company->name }}</td>
                            <td>{{ $company->email ?? '-' }}</td>
                            <td>{{ $company->phone ?? '-' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $company->branches_count }}</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('company.view')
                                        <a href="{{ route('admin.companies.show', $company) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('company.edit')
                                        <a href="{{ route('admin.companies.edit', $company) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('company.delete')
                                        <form action="{{ route('admin.companies.destroy', $company) }}" 
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
            {{ $companies->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('company.create')
            <div class="mt-3">
                <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('companies') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop

