@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('users'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('users') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $user->name }}" theme="primary" icon="fas fa-user">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('name') }}</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('email') }}</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('roles') }}</th>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-info">{{ $role->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('status') }}</th>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">{{ trans_common('active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if(isset($loginHistory) && $loginHistory->count() > 0)
            <div class="mt-3">
                <h5>{{ trans_common('login_history') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ trans_common('ip_address') }}</th>
                                <th>{{ trans_common('user_agent') }}</th>
                                <th>{{ trans_common('login_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loginHistory as $history)
                                <tr>
                                    <td>{{ $history->ip_address }}</td>
                                    <td>{{ Str::limit($history->user_agent, 50) }}</td>
                                    <td>{{ $history->login_at ? $history->login_at->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="mt-3">
            @can('user.edit')
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
