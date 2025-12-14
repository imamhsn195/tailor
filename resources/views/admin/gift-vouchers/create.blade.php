@extends('adminlte::page')

@section('title', trans_common('create') . ' ' . trans_common('gift_voucher'))

@section('content_header')
    <h1>{{ trans_common('create') }} {{ trans_common('gift_voucher') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('create') }} {{ trans_common('gift_voucher') }}" theme="primary" icon="fas fa-gift">
        <form action="{{ route('admin.gift-vouchers.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="voucher_code" 
                                      label="{{ trans_common('voucher_code') }}" 
                                      value="{{ old('voucher_code') }}" 
                                      placeholder="Leave empty to auto-generate" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name') }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="amount" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('amount') }}" 
                                      value="{{ old('amount') }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('customer') }}</label>
                        <select name="customer_id" class="form-control">
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->customer_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="issued_date" 
                                      type="date" 
                                      label="{{ trans_common('issued_date') }}" 
                                      value="{{ old('issued_date', date('Y-m-d')) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="expiry_date" 
                                      type="date" 
                                      label="{{ trans_common('expiry_date') }}" 
                                      value="{{ old('expiry_date') }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('status') }}</label>
                        <select name="status" class="form-control" required>
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach(\App\Enums\GiftVoucherStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ old('status') == $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <x-adminlte-textarea name="notes" 
                                 label="{{ trans_common('notes') }}" 
                                 rows="3">
                {{ old('notes') }}
            </x-adminlte-textarea>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.gift-vouchers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
