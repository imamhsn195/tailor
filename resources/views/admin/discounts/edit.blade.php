@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('discount'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('discount') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('discount') }}" theme="primary" icon="fas fa-percent">
        <form action="{{ route('admin.discounts.update', $discount) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name', $discount->name) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('type') }}</label>
                        <select name="type" class="form-control" required>
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach(\App\Enums\DiscountType::cases() as $type)
                                <option value="{{ $type->value }}" {{ old('type', $discount->type->value) == $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="value" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('value') }}" 
                                      value="{{ old('value', $discount->value) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('applicable_to') }}</label>
                        <select name="applicable_to" class="form-control" id="applicable_to">
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            <option value="customer_id" {{ old('applicable_to', $discount->applicable_to) == 'customer_id' ? 'selected' : '' }}>{{ trans_common('customer') }}</option>
                            <option value="membership" {{ old('applicable_to', $discount->applicable_to) == 'membership' ? 'selected' : '' }}>{{ trans_common('membership') }}</option>
                            <option value="product" {{ old('applicable_to', $discount->applicable_to) == 'product' ? 'selected' : '' }}>{{ trans_common('product') }}</option>
                            <option value="company" {{ old('applicable_to', $discount->applicable_to) == 'company' ? 'selected' : '' }}>{{ trans_common('company') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" id="customer_field" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('customer') }}</label>
                        <select name="customer_id" class="form-control">
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $discount->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->customer_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" id="membership_field" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('membership') }}</label>
                        <select name="membership_id" class="form-control">
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($memberships as $membership)
                                <option value="{{ $membership->id }}" {{ old('membership_id', $discount->membership_id) == $membership->id ? 'selected' : '' }}>
                                    {{ $membership->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" id="product_field" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('product') }}</label>
                        <select name="product_id" class="form-control">
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $discount->product_id) == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('branch') }}</label>
                        <select name="branch_id" class="form-control">
                            <option value="">{{ trans_common('all') }} {{ trans_common('branches') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $discount->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="start_date" 
                                      type="date" 
                                      label="{{ trans_common('start_date') }}" 
                                      value="{{ old('start_date', $discount->start_date?->format('Y-m-d')) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="end_date" 
                                      type="date" 
                                      label="{{ trans_common('end_date') }}" 
                                      value="{{ old('end_date', $discount->end_date?->format('Y-m-d')) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input-switch name="is_active" 
                                             label="{{ trans_common('status') }}" 
                                             checked="{{ old('is_active', $discount->is_active) }}" />
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('update') }}
                </button>
                <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#applicable_to').on('change', function() {
            $('#customer_field, #membership_field, #product_field').hide();
            if ($(this).val() === 'customer_id') {
                $('#customer_field').show();
            } else if ($(this).val() === 'membership') {
                $('#membership_field').show();
            } else if ($(this).val() === 'product') {
                $('#product_field').show();
            }
        });
        $('#applicable_to').trigger('change');
    });
</script>
@stop
