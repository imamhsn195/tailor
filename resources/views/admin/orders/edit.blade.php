@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('order'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('order') }}</h1>
@stop

@section('content')
    @if($order->cuttings()->exists())
        <x-adminlte-alert theme="warning" title="{{ trans_common('warning') }}">
            {{ trans_common('cannot_edit_order_after_cutting') }}
        </x-adminlte-alert>
    @endif

    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('order') }} - {{ $order->order_number }}" theme="primary" icon="fas fa-shopping-cart">
        <form action="{{ route('admin.orders.update', $order) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('customer') }} <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control select2" required>
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $order->customer_id) == $customer->id ? 'selected' : '' }}>
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
                                <option value="{{ $branch->id }}" {{ old('branch_id', $order->branch_id) == $branch->id ? 'selected' : '' }}>
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
                                      value="{{ old('order_date', $order->order_date->format('Y-m-d')) }}" 
                                      required />
                </div>
                <div class="col-md-4">
                    <x-adminlte-input name="trial_date" 
                                      type="date" 
                                      label="{{ trans_common('trial_date') }}" 
                                      value="{{ old('trial_date', $order->trial_date?->format('Y-m-d')) }}" />
                </div>
                <div class="col-md-4">
                    <x-adminlte-input name="delivery_date" 
                                      type="date" 
                                      label="{{ trans_common('delivery_date') }}" 
                                      value="{{ old('delivery_date', $order->delivery_date->format('Y-m-d')) }}" 
                                      required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="design_charge" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('design_charge') }}" 
                                      value="{{ old('design_charge', $order->design_charge) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="embroidery_charge" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('embroidery_charge') }}" 
                                      value="{{ old('embroidery_charge', $order->embroidery_charge) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="discount_amount" 
                                      type="number" 
                                      step="0.01" 
                                      min="0"
                                      label="{{ trans_common('discount') }}" 
                                      value="{{ old('discount_amount', $order->discount_amount) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>{{ trans_common('tailor_amount') }}:</strong>
                                    {{ number_format($order->tailor_amount, 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans_common('fabrics_amount') }}:</strong>
                                    {{ number_format($order->fabrics_amount, 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans_common('total_amount') }}:</strong>
                                    {{ number_format($order->total_amount, 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ trans_common('net_payable') }}:</strong>
                                    <span class="text-primary font-weight-bold">{{ number_format($order->net_payable, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($order->items->count() > 0)
                <hr>
                <h5>{{ trans_common('order_items') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ trans_common('product') }}</th>
                                <th>{{ trans_common('quantity') }}</th>
                                <th>{{ trans_common('unit_price') }}</th>
                                <th>{{ trans_common('total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($order->fabrics->count() > 0)
                <hr>
                <h5>{{ trans_common('fabrics') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ trans_common('fabric') }}</th>
                                <th>{{ trans_common('quantity') }}</th>
                                <th>{{ trans_common('unit_price') }}</th>
                                <th>{{ trans_common('total') }}</th>
                                <th>{{ trans_common('is_in_house') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->fabrics as $fabric)
                                <tr>
                                    <td>{{ $fabric->fabric_name }}</td>
                                    <td>{{ number_format($fabric->quantity, 2) }}</td>
                                    <td>{{ number_format($fabric->unit_price, 2) }}</td>
                                    <td>{{ number_format($fabric->total_price, 2) }}</td>
                                    <td>
                                        @if($fabric->is_in_house)
                                            <span class="badge badge-success">{{ trans_common('yes') }}</span>
                                        @else
                                            <span class="badge badge-info">{{ trans_common('no') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <x-adminlte-textarea name="notes" 
                                 label="{{ trans_common('notes') }}" 
                                 rows="3">
                {{ old('notes', $order->notes) }}
            </x-adminlte-textarea>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary" {{ $order->cuttings()->exists() ? 'disabled' : '' }}>
                    <i class="fas fa-save"></i> {{ trans_common('update') }}
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
    });
</script>
@stop
