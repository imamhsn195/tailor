@extends('adminlte::page')

@section('title', 'Create Tenant')

@section('content_header')
    <h1>Create Tenant</h1>
@stop

@section('content')
    <x-adminlte-card title="Create Tenant" theme="primary" icon="fas fa-building">
        <form action="{{ route('super-admin.tenants.store') }}" method="POST">
            @csrf

            <x-adminlte-input name="name" 
                              label="Tenant Name" 
                              value="{{ old('name') }}" 
                              required />

            <x-adminlte-input name="domain" 
                              label="Domain" 
                              value="{{ old('domain') }}" 
                              placeholder="example.com"
                              required />

            <x-adminlte-input name="database_name" 
                              label="Database Name" 
                              value="{{ old('database_name') }}" 
                              required />

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
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
