@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('coupon'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('coupon') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('coupon') }}" theme="primary" icon="fas fa-ticket-alt">
        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="code" 
                                      label="{{ trans_common('coupon_code') }}" 
                                      value="{{ old('code', $coupon->code) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name', $coupon->name) }}" 
                                      required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('type') }}</label>
                        <select name="type" class="form-control" required>
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach(\App\Enums\CouponType::cases() as $type)
                                <option value="{{ $type->value }}" {{ old('type', $coupon->type->value) == $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="value" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('value') }}" 
                                      value="{{ old('value', $coupon->value) }}" 
                                      required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="minimum_amount" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('minimum_amount') }}" 
                                      value="{{ old('minimum_amount', $coupon->minimum_amount) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="usage_limit" 
                                      type="number" 
                                      min="1"
                                      label="{{ trans_common('usage_limit') }}" 
                                      value="{{ old('usage_limit', $coupon->usage_limit) }}" 
                                      placeholder="Leave empty for unlimited" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="start_date" 
                                      type="date" 
                                      label="{{ trans_common('start_date') }}" 
                                      value="{{ old('start_date', $coupon->start_date?->format('Y-m-d')) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="end_date" 
                                      type="date" 
                                      label="{{ trans_common('end_date') }}" 
                                      value="{{ old('end_date', $coupon->end_date?->format('Y-m-d')) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input-switch name="is_active" 
                                             label="{{ trans_common('status') }}" 
                                             checked="{{ old('is_active', $coupon->is_active) }}" />
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('update') }}
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
