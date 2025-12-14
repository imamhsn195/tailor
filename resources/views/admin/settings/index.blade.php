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

    <!-- Settings Overview -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('total_users') }}" 
                                 text="{{ number_format($stats['total_users']) }}" 
                                 icon="fas fa-users" 
                                 theme="primary"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('active_users') }}" 
                                 text="{{ number_format($stats['active_users']) }}" 
                                 icon="fas fa-user-check" 
                                 theme="success"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('total_roles') }}" 
                                 text="{{ number_format($stats['total_roles']) }}" 
                                 icon="fas fa-user-shield" 
                                 theme="info"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="{{ trans_common('blocked_ips') }}" 
                                 text="{{ number_format($stats['blocked_ips']) }}" 
                                 icon="fas fa-ban" 
                                 theme="danger"/>
        </div>
    </div>

    <!-- User Management -->
    <x-adminlte-card title="{{ trans_common('user_management') }}" theme="primary" icon="fas fa-users">
        <div class="row">
            <div class="col-md-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-users"></i> {{ trans_common('manage_users') }}
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-user-shield"></i> {{ trans_common('manage_roles') }}
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-block mb-2">
                    <i class="fas fa-key"></i> {{ trans_common('permissions') }}
                </a>
            </div>
        </div>
    </x-adminlte-card>

    <!-- Security Settings -->
    <x-adminlte-card title="{{ trans_common('security_settings') }}" theme="warning" icon="fas fa-shield-alt">
        <div class="row">
            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-history"></i> {{ trans_common('view_login_history') }}
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.blocked-ips.index') }}" class="btn btn-danger btn-block mb-2">
                    <i class="fas fa-ban"></i> {{ trans_common('manage_blocked_ips') }}
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.blocked-macs.index') }}" class="btn btn-danger btn-block mb-2">
                    <i class="fas fa-ban"></i> {{ trans_common('manage_blocked_macs') }}
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary btn-block mb-2">
                    <i class="fas fa-list"></i> {{ trans_common('activity_logs') }}
                </a>
            </div>
        </div>
    </x-adminlte-card>

    <!-- Recent Logins -->
    @if($stats['recent_logins']->count() > 0)
        <x-adminlte-card title="{{ trans_common('recent_logins') }}" theme="info" icon="fas fa-clock">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>{{ trans_common('user') }}</th>
                            <th>{{ trans_common('ip_address') }}</th>
                            <th>{{ trans_common('login_at') }}</th>
                            <th>{{ trans_common('branch') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['recent_logins'] as $login)
                            <tr>
                                <td>{{ $login->user->name ?? '-' }}</td>
                                <td>{{ $login->ip_address }}</td>
                                <td>{{ $login->login_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $login->branch->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-adminlte-card>
    @endif
@stop
