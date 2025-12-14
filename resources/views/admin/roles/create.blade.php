@extends('adminlte::page')

@section('title', trans_common('create') . ' ' . trans_common('roles'))

@section('content_header')
    <h1>{{ trans_common('create') }} {{ trans_common('roles') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('create') }} {{ trans_common('roles') }}" theme="primary" icon="fas fa-user-shield">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf

            <x-adminlte-input name="name" 
                              label="{{ trans_common('name') }}" 
                              value="{{ old('name') }}" 
                              required />

            <div class="form-group">
                <label>{{ trans_common('permissions') }}</label>
                <div class="row">
                    @foreach($permissions as $permission)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="permissions[]" 
                                       value="{{ $permission->id }}" 
                                       id="permission_{{ $permission->id }}"
                                       {{ old('permissions') && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
