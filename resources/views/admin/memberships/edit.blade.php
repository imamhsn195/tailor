@extends('adminlte::page')

@section('title', trans_common('edit') . ' ' . trans_common('membership'))

@section('content_header')
    <h1>{{ trans_common('edit') }} {{ trans_common('membership') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('edit') }} {{ trans_common('membership') }}" theme="primary" icon="fas fa-id-card">
        <form action="{{ route('admin.memberships.update', $membership) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="name" 
                                      label="{{ trans_common('name') }}" 
                                      value="{{ old('name', $membership->name) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ trans_common('type') }}</label>
                        <select name="type" class="form-control" required>
                            <option value="">{{ trans_common('select_an_option') }}</option>
                            @foreach(\App\Enums\MembershipType::cases() as $type)
                                <option value="{{ $type->value }}" {{ old('type', $membership->type->value) == $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input name="discount_percentage" 
                                      type="number" 
                                      step="0.01" 
                                      min="0" 
                                      max="100"
                                      label="{{ trans_common('discount_percentage') }}" 
                                      value="{{ old('discount_percentage', $membership->discount_percentage) }}" 
                                      required />
                </div>
                <div class="col-md-6">
                    <x-adminlte-input-switch name="is_active" 
                                             label="{{ trans_common('status') }}" 
                                             checked="{{ old('is_active', $membership->is_active) }}" />
                </div>
            </div>

            <x-adminlte-textarea name="description" 
                                 label="{{ trans_common('description') }}" 
                                 rows="3">
                {{ old('description', $membership->description) }}
            </x-adminlte-textarea>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans_common('update') }}
                </button>
                <a href="{{ route('admin.memberships.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ trans_common('cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop
