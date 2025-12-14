@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('blocked_ips'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('blocked_ips') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $blockedIp->ip_address }}" theme="primary" icon="fas fa-ban">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('ip_address') }}</th>
                        <td><code>{{ $blockedIp->ip_address }}</code></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('reason') }}</th>
                        <td>{{ $blockedIp->reason ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('blocked_at') }}</th>
                        <td>{{ $blockedIp->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            @can('blocked-ip.edit')
                <a href="{{ route('admin.blocked-ips.edit', $blockedIp) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.blocked-ips.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
