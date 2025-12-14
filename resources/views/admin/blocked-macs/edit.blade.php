@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('blocked_macs'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('blocked_macs') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('blocked_macs') }}" theme="primary" icon="fas fa-ban">
        <form action="{{ route('admin.blocked-macs.update', $blockedMac) }}" method="POST">
            @csrf
            @method('PUT')

            <x-adminlte-input name="mac_address" 
                              label="{{ trans_common('mac_address') }}" 
                              value="{{ old('mac_address', $blockedMac->mac_address) }}" 
                              required />

            <x-adminlte-textarea name="reason" 
                                 label="{{ trans_common('reason') }}" 
                                 rows="3">
                {{ old('reason', $blockedMac->reason) }}
            </x-adminlte-textarea>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.blocked-macs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
