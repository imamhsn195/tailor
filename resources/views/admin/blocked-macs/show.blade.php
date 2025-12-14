@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('blocked_macs'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('blocked_macs') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $blockedMac->mac_address }}" theme="primary" icon="fas fa-ban">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('mac_address') }}</th>
                        <td><code>{{ $blockedMac->mac_address }}</code></td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('reason') }}</th>
                        <td>{{ $blockedMac->reason ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('blocked_at') }}</th>
                        <td>{{ $blockedMac->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            @can('blocked-mac.edit')
                <a href="{{ route('admin.blocked-macs.edit', $blockedMac) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.blocked-macs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
