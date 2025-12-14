@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('permissions'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('permissions') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $permission->name }}" theme="primary" icon="fas fa-key">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('name') }}</th>
                        <td>{{ $permission->name }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
