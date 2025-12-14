@extends('adminlte::page')

@section('title', trans_common('system_settings'))

@section('content_header')
    <h1>{{ trans_common('system_settings') }}</h1>
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

    <x-adminlte-card title="{{ trans_common('system_settings') }}" theme="primary" icon="fas fa-cog">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="company_name" 
                                      label="{{ trans_common('company_name') }}" 
                                      value="{{ old('company_name', $settings['company_name'] ?? '') }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="timezone" 
                                      label="{{ trans_common('timezone') }}" 
                                      value="{{ old('timezone', $settings['timezone'] ?? '') }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-select name="locale" label="{{ trans_common('language') }}">
                        <option value="en" {{ old('locale', $settings['locale'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="bn" {{ old('locale', $settings['locale'] ?? 'en') === 'bn' ? 'selected' : '' }}>বাংলা</option>
                    </x-adminlte-select>
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="currency" 
                                      label="{{ trans_common('currency') }}" 
                                      value="{{ old('currency', $settings['currency'] ?? '') }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="date_format" 
                                      label="{{ trans_common('date_format') }}" 
                                      value="{{ old('date_format', $settings['date_format'] ?? '') }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="time_format" 
                                      label="{{ trans_common('time_format') }}" 
                                      value="{{ old('time_format', $settings['time_format'] ?? '') }}" />
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>

    <!-- Security Settings -->
    <x-adminlte-card title="{{ trans_common('security_settings') }}" theme="warning" icon="fas fa-shield-alt">
        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('admin.blocked-ips.index') }}" class="btn btn-warning btn-block">
                    <i class="fas fa-ban"></i> {{ trans_common('manage') }} {{ trans_common('blocked_ips') }}
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('admin.blocked-macs.index') }}" class="btn btn-warning btn-block">
                    <i class="fas fa-ban"></i> {{ trans_common('manage') }} {{ trans_common('blocked_macs') }}
                </a>
            </div>
        </div>
    </x-adminlte-card>
@stop
