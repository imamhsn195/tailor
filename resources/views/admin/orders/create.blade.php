@extends('adminlte::page')

@section('title', trans_common('create') . ' ' . trans_common('order'))

@section('content_header')
    <h1>{{ trans_common('create') }} {{ trans_common('order') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('create') }} {{ trans_common('order') }}" theme="primary" icon="fas fa-shopping-cart">
        <form action="{{ route('admin.orders.store') }}" method="POST" id="order-form">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('customer') }} <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control select2" required>
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->customer_id }}) - {{ $customer->mobile ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('branch') }} <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-control" required>
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-adminlte-input name="order_date" 
                                      type="date" 
                                      label="{{ trans_common('order_date') }}" 
                                      value="{{ old('order_date', date('Y-m-d')) }}" 
                                      required />
                </div>
                <div class="col-md-4">
                    <x-adminlte-input name="trial_date" 
                                      type="date" 
                                      label="{{ trans_common('trial_date') }}" 
                                      value="{{ old('trial_date') }}" />
                </div>
                <div class="col-md-4">
                    <x-adminlte-input name="delivery_date" 
                                      type="date" 
                                      label="{{ trans_common('delivery_date') }}" 
                                      value="{{ old('delivery_date') }}" 
                                      required />
                </div>
            </div>

            <hr>
            <h5>{{ trans_common('order_items') }}</h5>
            <div id="items-container">
                <div class="item-row mb-3 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>{{ trans_common('product') }} <span class="text-danger">*</span></label>
                                <select name="items[0][product_id]" class="form-control product-select" required>
                                    <option value="">{{ trans_common('select_an_option') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->sale_price }}">
                                            {{ $product->name }} - {{ number_format($product->sale_price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('quantity') }} <span class="text-danger">*</span></label>
                                <input type="number" name="items[0][quantity]" class="form-control item-quantity" min="1" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('unit_price') }} <span class="text-danger">*</span></label>
                                <input type="number" name="items[0][unit_price]" class="form-control item-price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('total') }}</label>
                                <input type="text" class="form-control item-total" readonly value="0.00">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-block remove-item" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <x-adminlte-input name="items[0][notes]" 
                                              label="{{ trans_common('notes') }}" 
                                              value="{{ old('items.0.notes') }}" />
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary mb-3" id="add-item">
                <i class="fas fa-plus"></i> {{ trans_common('add') }} {{ trans_common('item') }}
            </button>

            <hr>
            <h5>{{ trans_common('fabrics') }}</h5>
            <div id="fabrics-container">
                <div class="fabric-row mb-3 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ trans_common('fabric') }}</label>
                                <select name="fabrics[0][product_id]" class="form-control fabric-product-select">
                                    <option value="">{{ trans_common('select_an_option') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->sale_price }}">
                                            {{ $product->name }} - {{ number_format($product->sale_price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('quantity') }}</label>
                                <input type="number" name="fabrics[0][quantity]" class="form-control fabric-quantity" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('unit_price') }}</label>
                                <input type="number" name="fabrics[0][unit_price]" class="form-control fabric-price" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('total') }}</label>
                                <input type="text" class="form-control fabric-total" readonly value="0.00">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('is_in_house') }}</label>
                                <div class="form-check">
                                    <input type="checkbox" name="fabrics[0][is_in_house]" class="form-check-input" value="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary mb-3" id="add-fabric">
                <i class="fas fa-plus"></i> {{ trans_common('add') }} {{ trans_common('fabric') }}
            </button>

            <hr>
            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="design_charge" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('design_charge') }}" 
                                      value="{{ old('design_charge', 0) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="embroidery_charge" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('embroidery_charge') }}" 
                                      value="{{ old('embroidery_charge', 0) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="discount_amount" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('discount') }}" 
                                      value="{{ old('discount_amount', 0) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>{{ trans_common('tailor_amount') }}:</strong>
                                    <span id="tailor-amount">0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans_common('fabrics_amount') }}:</strong>
                                    <span id="fabrics-amount">0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans_common('total_amount') }}:</strong>
                                    <span id="total-amount">0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans_common('net_payable') }}:</strong>
                                    <span id="net-payable" class="text-primary font-weight-bold">0.00</span>
                                </div>
                            </div>
                        </div>
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
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        let itemIndex = 1;
        let fabricIndex = 1;

        // Add item row
        $('#add-item').on('click', function() {
            const itemRow = `
                <div class="item-row mb-3 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>{{ trans_common('product') }} <span class="text-danger">*</span></label>
                                <select name="items[${itemIndex}][product_id]" class="form-control product-select" required>
                                    <option value="">{{ trans_common('select_an_option') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">
                                            {{ $product->name }} - {{ number_format($product->sale_price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('quantity') }} <span class="text-danger">*</span></label>
                                <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" min="1" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('unit_price') }} <span class="text-danger">*</span></label>
                                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control item-price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('total') }}</label>
                                <input type="text" class="form-control item-total" readonly value="0.00">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-block remove-item">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="items[${itemIndex}][notes]" class="form-control" placeholder="{{ trans_common('notes') }}">
                        </div>
                    </div>
                </div>
            `;
            $('#items-container').append(itemRow);
            itemIndex++;
            updateRemoveButtons();
        });

        // Add fabric row
        $('#add-fabric').on('click', function() {
            const fabricRow = `
                <div class="fabric-row mb-3 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ trans_common('fabric') }}</label>
                                <select name="fabrics[${fabricIndex}][product_id]" class="form-control fabric-product-select">
                                    <option value="">{{ trans_common('select_an_option') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">
                                            {{ $product->name }} - {{ number_format($product->sale_price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('quantity') }}</label>
                                <input type="number" name="fabrics[${fabricIndex}][quantity]" class="form-control fabric-quantity" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('unit_price') }}</label>
                                <input type="number" name="fabrics[${fabricIndex}][unit_price]" class="form-control fabric-price" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('total') }}</label>
                                <input type="text" class="form-control fabric-total" readonly value="0.00">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ trans_common('is_in_house') }}</label>
                                <div class="form-check">
                                    <input type="checkbox" name="fabrics[${fabricIndex}][is_in_house]" class="form-check-input" value="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#fabrics-container').append(fabricRow);
            fabricIndex++;
        });

        // Remove item
        $(document).on('click', '.remove-item', function() {
            $(this).closest('.item-row').remove();
            calculateTotals();
            updateRemoveButtons();
        });

        // Product select change - set price
        $(document).on('change', '.product-select', function() {
            const price = $(this).find('option:selected').data('price') || 0;
            $(this).closest('.item-row').find('.item-price').val(price);
            calculateItemTotal($(this).closest('.item-row'));
            calculateTotals();
        });

        // Fabric product select change
        $(document).on('change', '.fabric-product-select', function() {
            const price = $(this).find('option:selected').data('price') || 0;
            $(this).closest('.fabric-row').find('.fabric-price').val(price);
            calculateFabricTotal($(this).closest('.fabric-row'));
            calculateTotals();
        });

        // Calculate item total
        $(document).on('input', '.item-quantity, .item-price', function() {
            calculateItemTotal($(this).closest('.item-row'));
            calculateTotals();
        });

        // Calculate fabric total
        $(document).on('input', '.fabric-quantity, .fabric-price', function() {
            calculateFabricTotal($(this).closest('.fabric-row'));
            calculateTotals();
        });

        // Calculate discount and totals
        $('input[name="design_charge"], input[name="embroidery_charge"], input[name="discount_amount"]').on('input', function() {
            calculateTotals();
        });

        function calculateItemTotal(row) {
            const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
            const price = parseFloat(row.find('.item-price').val()) || 0;
            const total = quantity * price;
            row.find('.item-total').val(total.toFixed(2));
        }

        function calculateFabricTotal(row) {
            const quantity = parseFloat(row.find('.fabric-quantity').val()) || 0;
            const price = parseFloat(row.find('.fabric-price').val()) || 0;
            const total = quantity * price;
            row.find('.fabric-total').val(total.toFixed(2));
        }

        function calculateTotals() {
            let tailorAmount = 0;
            $('.item-row').each(function() {
                const total = parseFloat($(this).find('.item-total').val()) || 0;
                tailorAmount += total;
            });

            let fabricsAmount = 0;
            $('.fabric-row').each(function() {
                const total = parseFloat($(this).find('.fabric-total').val()) || 0;
                fabricsAmount += total;
            });

            const designCharge = parseFloat($('input[name="design_charge"]').val()) || 0;
            const embroideryCharge = parseFloat($('input[name="embroidery_charge"]').val()) || 0;
            const discountAmount = parseFloat($('input[name="discount_amount"]').val()) || 0;

            const totalAmount = tailorAmount + fabricsAmount + designCharge + embroideryCharge;
            const netPayable = totalAmount - discountAmount;

            $('#tailor-amount').text(tailorAmount.toFixed(2));
            $('#fabrics-amount').text(fabricsAmount.toFixed(2));
            $('#total-amount').text(totalAmount.toFixed(2));
            $('#net-payable').text(netPayable.toFixed(2));
        }

        function updateRemoveButtons() {
            const itemRows = $('.item-row');
            if (itemRows.length > 1) {
                $('.remove-item').show();
            } else {
                $('.remove-item').hide();
            }
        }

        // Initial calculation
        calculateTotals();
        updateRemoveButtons();
    });
</script>
@stop
