@extends('adminlte::page')

@section('title', trans_common('blocked_macs'))

@section('content_header')
    <h1>{{ trans_common('blocked_macs') }}</h1>
@stop

@section('content')
    @if(session('success'))
        <x-adminlte-alert theme="success" title="{{ trans_common('success') }}">
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    @if(session('error'))
        <x-adminlte-alert theme="danger" title="{{ trans_common('error') }}">
            {{ session('error') }}
        </x-adminlte-alert>
    @endif

    <x-adminlte-card title="{{ trans_common('blocked_macs') }}" theme="primary" icon="fas fa-ban">
        @include('components.search-form', ['route' => route('admin.blocked-macs.index')])
        
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
                        <th>{{ trans_common('mac_address') }}</th>
                        <th>{{ trans_common('reason') }}</th>
                        <th>{{ trans_common('blocked_by') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('created_at') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blockedMacs as $blockedMac)
                        <tr>
                            <td>{{ $blockedMac->id }}</td>
                            <td><code>{{ $blockedMac->mac_address }}</code></td>
                            <td>{{ $blockedMac->reason ?? '-' }}</td>
                            <td>{{ $blockedMac->blockedBy->name ?? '-' }}</td>
                            <td>
                                @if($blockedMac->is_active)
                                    <span class="badge badge-danger">{{ trans_common('active') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ trans_common('inactive') }}</span>
                                @endif
                            </td>
                            <td>{{ $blockedMac->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('settings.view')
                                        <a href="{{ route('admin.blocked-macs.show', $blockedMac) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('settings.edit')
                                        <a href="{{ route('admin.blocked-macs.edit', $blockedMac) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('settings.edit')
                                        <form action="{{ route('admin.blocked-macs.destroy', $blockedMac) }}" 
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
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $blockedMacs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('settings.edit')
            <div class="mt-3">
                <a href="{{ route('admin.blocked-macs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('blocked_mac') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop
