@extends('adminlte::page')

@section('title', trans_common('create') . ' ' . trans_common('products'))

@section('content_header')
    <h1>{{ trans_common('create') }} {{ trans_common('products') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('create') }} {{ trans_common('products') }}" theme="primary" icon="fas fa-box">
        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name') }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('category') }}</label>
                        <select name="category_id" class="form-control">
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('unit') }} <span class="text-danger">*</span></label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->abbreviation }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="brand" 
                                      label="{{ trans_common('brand') }}" 
                                      value="{{ old('brand') }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="purchase_price" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('purchase_price') }}" 
                                      value="{{ old('purchase_price', 0) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="sale_price" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('sale_price') }}" 
                                      value="{{ old('sale_price') }}" 
                                      required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="fabric_width" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('fabric_width') }}" 
                                      value="{{ old('fabric_width') }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="vat_percentage" 
                                      type="number" 
                                      step="0.01" 
                                      min="0" 
                                      max="100"
                                      label="{{ trans_common('vat_percentage') }}" 
                                      value="{{ old('vat_percentage', 0) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('vat_type') }}</label>
                        <select name="vat_type" class="form-control">
                            <option value="inclusive" {{ old('vat_type', 'inclusive') == 'inclusive' ? 'selected' : '' }}>
                                Inclusive
                            </option>
                            <option value="exclusive" {{ old('vat_type') == 'exclusive' ? 'selected' : '' }}>
                                Exclusive
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="low_stock_alert" 
                                      type="number" 
                                      min="0"
                                      label="{{ trans_common('low_stock_alert') }}" 
                                      value="{{ old('low_stock_alert', 0) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="barcode" 
                                      label="{{ trans_common('barcode') }}" 
                                      value="{{ old('barcode') }}" />
                    <small class="form-text text-muted">Leave empty to auto-generate</small>
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="qr_code" 
                                      label="{{ trans_common('qr_code') }}" 
                                      value="{{ old('qr_code') }}" />
                    <small class="form-text text-muted">Leave empty to auto-generate</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input-switch name="generate_barcode" 
                                             label="{{ trans_common('generate_barcode') }}" 
                                             checked="{{ old('generate_barcode', false) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input-switch name="generate_qr_code" 
                                             label="{{ trans_common('generate_qr_code') }}" 
                                             checked="{{ old('generate_qr_code', false) }}" />
                </div>
            </div>

            <x-adminlte-textarea name="description" 
                                 label="{{ trans_common('description') }}" 
                                 rows="3">
                {{ old('description') }}
            </x-adminlte-textarea>

            <div class="form-group">
                <label>{{ trans_common('sizes') }}</label>
                <div id="sizes-container">
                    <div class="input-group mb-2">
                        <input type="text" name="sizes[]" class="form-control" placeholder="Enter size">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger remove-size" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" id="add-size">
                    <i class="fas fa-plus"></i> {{ trans_common('add') }} {{ trans_common('size') }}
                </button>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input-switch name="is_active" 
                                             label="{{ trans_common('status') }}" 
                                             checked="{{ old('is_active', true) }}" />
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('save') }}
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#add-size').on('click', function() {
            const sizeInput = `
                <div class="input-group mb-2">
                    <input type="text" name="sizes[]" class="form-control" placeholder="Enter size">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-size">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#sizes-container').append(sizeInput);
            updateRemoveButtons();
        });

        $(document).on('click', '.remove-size', function() {
            $(this).closest('.input-group').remove();
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            const sizeGroups = $('#sizes-container .input-group');
            if (sizeGroups.length > 1) {
                $('.remove-size').show();
            } else {
                $('.remove-size').hide();
            }
        }
    });
</script>
@stop
