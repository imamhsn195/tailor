@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('customer'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('customer') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('customer') }}" theme="primary" icon="fas fa-user-edit">
        <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name', $customer->name) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="mobile" 
                                      label="{{ trans_common('mobile') }}" 
                                      value="{{ old('mobile', $customer->mobile) }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="phone" 
                                      label="{{ trans_common('phone') }}" 
                                      value="{{ old('phone', $customer->phone) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input name="email" 
                                      type="email" 
                                      label="{{ trans_common('email') }}" 
                                      value="{{ old('email', $customer->email) }}" />
                </div>
            </div>

            <x-adminlte-textarea name="address" 
                                 label="{{ trans_common('address') }}" 
                                 rows="3">
                {{ old('address', $customer->address) }}
            </x-adminlte-textarea>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="discount_percentage" 
                                      type="number" 
                                      step="0.01" 
                                      min="0" 
                                      max="100"
                                      label="{{ trans_common('discount_percentage') }}" 
                                      value="{{ old('discount_percentage', $customer->discount_percentage) }}" />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input-switch name="is_active" 
                                             label="{{ trans_common('status') }}" 
                                             checked="{{ old('is_active', $customer->is_active) }}" />
                </div>
            </div>

            <x-adminlte-textarea name="comments" 
                                 label="{{ trans_common('comments') }}" 
                                 rows="3">
                {{ old('comments', $customer->comments) }}
            </x-adminlte-textarea>

            <div class="form-group">
                <label>{{ trans_common('memberships') }}</label>
                <select name="memberships[]" class="form-control select2" multiple>
                    @foreach($memberships as $membership)
                        <option value="{{ $membership->id }}" {{ in_array($membership->id, old('memberships', $customer->memberships->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $membership->name }} ({{ $membership->discount_percentage }}%)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('update') }}
                </button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
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
            theme: 'bootstrap4',
            placeholder: '{{ trans_common("select") }} {{ trans_common("memberships") }}'
        });
    });
</script>
@stop
