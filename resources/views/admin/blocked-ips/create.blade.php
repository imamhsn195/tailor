@extends('adminlte::page')

@section('title', trans_common('create') . ' ' . trans_common('blocked_ips'))

@section('content_header')
    <h1>{{ trans_common('create') }} {{ trans_common('blocked_ips') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('create') }} {{ trans_common('blocked_ips') }}" theme="primary" icon="fas fa-ban">
        <form action="{{ route('admin.blocked-ips.store') }}" method="POST">
            @csrf

            <x-adminlte-input name="ip_address" 
                              label="{{ trans_common('ip_address') }}" 
                              value="{{ old('ip_address') }}" 
                              placeholder="192.168.1.1"
                              required />

            <x-adminlte-textarea name="reason" 
                                 label="{{ trans_common('reason') }}" 
                                 rows="3">
                {{ old('reason') }}
            </x-adminlte-textarea>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.blocked-ips.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
