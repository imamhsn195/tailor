@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('users'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('users') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('users') }}" theme="primary" icon="fas fa-user-edit">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name', $user->name) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="email" 
                                      type="email" 
                                      label="{{ trans_common('email') }}" 
                                      value="{{ old('email', $user->email) }}" 
                                      required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="password" 
                                      type="password" 
                                      label="{{ trans_common('password') }}" 
                                      placeholder="{{ trans_common('leave_blank_to_keep_current') }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="password_confirmation" 
                                      type="password" 
                                      label="{{ trans_common('confirm_password') }}" />
                </div>
            </div>

            <div class="form-group">
                <label>{{ trans_common('roles') }}</label>
                @foreach($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="roles[]" 
                               value="{{ $role->id }}" 
                               id="role_{{ $role->id }}"
                               {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ $role->name }}
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="form-group">
                <x-adminlte-input-switch name="is_active" 
                                         label="{{ trans_common('status') }}" 
                                         checked="{{ old('is_active', $user->is_active) }}" />
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
