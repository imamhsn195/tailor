@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('suppliers'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('suppliers') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('suppliers') }}" theme="primary" icon="fas fa-truck">
        <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name', $supplier->name) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="contact_person" 
                                      label="{{ trans_common('contact_person') }}" 
                                      value="{{ old('contact_person', $supplier->contact_person) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="mobile" 
                                      label="{{ trans_common('mobile') }}" 
                                      value="{{ old('mobile', $supplier->mobile) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="phone" 
                                      label="{{ trans_common('phone') }}" 
                                      value="{{ old('phone', $supplier->phone) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="email" 
                                      type="email" 
                                      label="{{ trans_common('email') }}" 
                                      value="{{ old('email', $supplier->email) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="vat_no" 
                                      label="{{ trans_common('vat_no') }}" 
                                      value="{{ old('vat_no', $supplier->vat_no) }}" />
                </div>
            </div>

            <x-adminlte-textarea name="address" 
                                 label="{{ trans_common('address') }}" 
                                 rows="3">
                {{ old('address', $supplier->address) }}
            </x-adminlte-textarea>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="discount_percentage" 
                                      type="number" 
                                      step="0.01" 
                                      min="0" 
                                      max="100"
                                      label="{{ trans_common('discount_percentage') }}" 
                                      value="{{ old('discount_percentage', $supplier->discount_percentage) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input-switch name="is_active" 
                                             label="{{ trans_common('status') }}" 
                                             checked="{{ old('is_active', $supplier->is_active) }}" />
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
