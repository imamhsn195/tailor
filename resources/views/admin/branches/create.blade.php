@extends('adminlte::page')

@section('title', trans_common('create') . ' ' . trans_common('branches'))

@section('content_header')
    <h1>{{ trans_common('create') }} {{ trans_common('branches') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('create') }} {{ trans_common('branches') }}" theme="primary" icon="fas fa-sitemap">
        <form action="{{ route('admin.branches.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-select name="company_id" label="{{ trans_common('company') }}">
                        <option value="">{{ trans_common('select_an_option') }}</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </x-adminlte-select>
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="branch_id" 
                                      label="{{ trans_common('branch_id') }}" 
                                      value="{{ old('branch_id') }}" 
                                      required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name') }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="email" 
                                      type="email" 
                                      label="{{ trans_common('email') }}" 
                                      value="{{ old('email') }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="phone" 
                                      label="{{ trans_common('phone') }}" 
                                      value="{{ old('phone') }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="e_bin" 
                                      label="{{ trans_common('e_bin') }}" 
                                      value="{{ old('e_bin') }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="bin" 
                                      label="{{ trans_common('bin') }}" 
                                      value="{{ old('bin') }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="trade_license_no" 
                                      label="{{ trans_common('trade_license_no') }}" 
                                      value="{{ old('trade_license_no') }}" />
                </div>
            </div>

            <x-adminlte-textarea name="address" 
                                 label="{{ trans_common('address') }}" 
                                 rows="3">
                {{ old('address') }}
            </x-adminlte-textarea>

            <div class="form-group">
                <label>{{ trans_common('modules') }}</label>
                <div>
                    @foreach($modules as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="modules[]" 
                                   value="{{ $key }}" 
                                   id="module_{{ $key }}"
                                   {{ in_array($key, old('modules', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="module_{{ $key }}">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="is_active" 
                           value="1" 
                           id="is_active"
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        {{ trans_common('active') }}
                    </label>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop

