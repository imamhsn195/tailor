@extends('adminlte::page')

@section('title', 'Create Tenant')

@section('content_header')
    <h1>Create Tenant</h1>
@stop

@section('content')
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="Error">
            {{ session('error') }}
        </x-adminlte-alert>
    @endif

    <x-adminlte-card title="Create Tenant" theme="primary" icon="fas fa-building">
        <form action="{{ route('super-admin.tenants.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="Tenant Name" 
                                      value="{{ old('name') }}" 
                                      placeholder="Company Name"
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="domain" 
                                      label="Domain" 
                                      value="{{ old('domain') }}" 
                                      placeholder="example.com"
                                      required />
                    <small class="form-text text-muted">Must be unique. Used for tenant identification.</small>
                </div>
            </div>

            <hr>
            <h5>Admin User Credentials</h5>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="email" 
                                      type="email"
                                      label="Admin Email" 
                                      value="{{ old('email') }}" 
                                      placeholder="admin@example.com"
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="password" 
                                      type="password"
                                      label="Admin Password" 
                                      value="{{ old('password') }}" 
                                      placeholder="Minimum 8 characters"
                                      required />
                    <small class="form-text text-muted">Minimum 8 characters</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="trial_days" 
                                      type="number"
                                      label="Trial Days (Optional)" 
                                      value="{{ old('trial_days', 0) }}" 
                                      min="0"
                                      placeholder="0" />
                    <small class="form-text text-muted">Number of trial days. Leave 0 for no trial.</small>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Note:</strong> The tenant database will be created automatically with all migrations and default seeders.
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Tenant
                </button>
                <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
