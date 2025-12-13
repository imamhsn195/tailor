@extends('adminlte::page')

@section('title', trans_common('branches'))

@section('content_header')
    <h1>{{ trans_common('branches') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('branches') }}" theme="primary" icon="fas fa-sitemap">
        @include('components.search-form', ['route' => route('admin.branches.index')])
        
        <div class="row mb-3">
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
                        <th>{{ trans_common('branch_id') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('company') }}</th>
                        <th>{{ trans_common('modules') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td>{{ $branch->id }}</td>
                            <td>{{ $branch->branch_id }}</td>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->company->name ?? '-' }}</td>
                            <td>
                                @if($branch->modules)
                                    @foreach($branch->modules as $module)
                                        <span class="badge badge-info">{{ ucfirst($module) }}</span>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($branch->is_active)
                                    <span class="badge badge-success">{{ trans_common('active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('branch.view')
                                        <a href="{{ route('admin.branches.show', $branch) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('branch.edit')
                                        <a href="{{ route('admin.branches.edit', $branch) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('branch.delete')
                                        <form action="{{ route('admin.branches.destroy', $branch) }}" 
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
            {{ $branches->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('branch.create')
            <div class="mt-3">
                <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('branches') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop

