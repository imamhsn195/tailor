@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('roles'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('roles') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $role->name }}" theme="primary" icon="fas fa-user-shield">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('name') }}</th>
                        <td>{{ $role->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('permissions') }}</th>
                        <td>
                            @foreach($role->permissions as $permission)
                                <span class="badge badge-secondary">{{ $permission->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            @can('role.edit')
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
