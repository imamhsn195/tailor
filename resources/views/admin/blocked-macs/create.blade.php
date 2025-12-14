@extends('adminlte::page')

@section('title', trans_common('create') . ' ' . trans_common('blocked_macs'))

@section('content_header')
    <h1>{{ trans_common('create') }} {{ trans_common('blocked_macs') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('create') }} {{ trans_common('blocked_macs') }}" theme="primary" icon="fas fa-ban">
        <form action="{{ route('admin.blocked-macs.store') }}" method="POST">
            @csrf

            <x-adminlte-input name="mac_address" 
                              label="{{ trans_common('mac_address') }}" 
                              value="{{ old('mac_address') }}" 
                              placeholder="00:1B:44:11:3A:B7"
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
                <a href="{{ route('admin.blocked-macs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
