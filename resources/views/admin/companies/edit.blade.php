@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('companies'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('companies') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('companies') }}" theme="primary" icon="fas fa-building">
        <form action="{{ route('admin.companies.update', $company) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name', $company->name) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="branch_name" 
                                      label="{{ trans_common('branch_name') }}" 
                                      value="{{ old('branch_name', $company->branch_name) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="email" 
                                      type="email" 
                                      label="{{ trans_common('email') }}" 
                                      value="{{ old('email', $company->email) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="phone" 
                                      label="{{ trans_common('phone') }}" 
                                      value="{{ old('phone', $company->phone) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="mobile" 
                                      label="{{ trans_common('mobile') }}" 
                                      value="{{ old('mobile', $company->mobile) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="website" 
                                      type="url" 
                                      label="{{ trans_common('website') }}" 
                                      value="{{ old('website', $company->website) }}" />
                </div>
            </div>

            <x-adminlte-textarea name="address" 
                                 label="{{ trans_common('address') }}" 
                                 rows="3">
                {{ old('address', $company->address) }}
            </x-adminlte-textarea>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="invoice_name" 
                                      label="{{ trans_common('invoice_name') }}" 
                                      value="{{ old('invoice_name', $company->invoice_name) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="company_registration_no" 
                                      label="{{ trans_common('company_registration_no') }}" 
                                      value="{{ old('company_registration_no', $company->company_registration_no) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="company_tin_no" 
                                      label="{{ trans_common('company_tin_no') }}" 
                                      value="{{ old('company_tin_no', $company->company_tin_no) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="e_bin" 
                                      label="{{ trans_common('e_bin') }}" 
                                      value="{{ old('e_bin', $company->e_bin) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="bin" 
                                      label="{{ trans_common('bin') }}" 
                                      value="{{ old('bin', $company->bin) }}" />
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('update') }}
                </button>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop

