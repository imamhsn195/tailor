@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('branches'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('branches') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('branches') }}" theme="primary" icon="fas fa-sitemap">
        <form action="{{ route('admin.branches.update', $branch) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-select name="company_id" label="{{ trans_common('company') }}">
                        <option value="">{{ trans_common('select_an_option') }}</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $branch->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </x-adminlte-select>
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="branch_id" 
                                      label="{{ trans_common('branch_id') }}" 
                                      value="{{ old('branch_id', $branch->branch_id) }}" 
                                      required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name', $branch->name) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="email" 
                                      type="email" 
                                      label="{{ trans_common('email') }}" 
                                      value="{{ old('email', $branch->email) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="phone" 
                                      label="{{ trans_common('phone') }}" 
                                      value="{{ old('phone', $branch->phone) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="e_bin" 
                                      label="{{ trans_common('e_bin') }}" 
                                      value="{{ old('e_bin', $branch->e_bin) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="bin" 
                                      label="{{ trans_common('bin') }}" 
                                      value="{{ old('bin', $branch->bin) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="trade_license_no" 
                                      label="{{ trans_common('trade_license_no') }}" 
                                      value="{{ old('trade_license_no', $branch->trade_license_no) }}" />
                </div>
            </div>

            <x-adminlte-textarea name="address" 
                                 label="{{ trans_common('address') }}" 
                                 rows="3">
                {{ old('address', $branch->address) }}
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
                                   {{ in_array($key, old('modules', $branch->modules ?? [])) ? 'checked' : '' }}>
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
                           {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        {{ trans_common('active') }}
                    </label>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('update') }}
                </button>
                <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop

