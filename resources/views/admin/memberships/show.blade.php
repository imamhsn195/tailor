@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('membership'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('membership') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $membership->name }}" theme="primary" icon="fas fa-id-card">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('name') }}</th>
                        <td>{{ $membership->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('type') }}</th>
                        <td>{{ $membership->type->label() }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('discount_percentage') }}</th>
                        <td>{{ $membership->discount_percentage }}%</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('status') }}</th>
                        <td>
                            @if($membership->is_active)
                                <span class="badge badge-success">{{ trans_common('active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('description') }}</th>
                        <td>{{ $membership->description ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('customers') }}</th>
                        <td>
                            <span class="badge badge-info">{{ $membership->customers->count() }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('created_at') }}</th>
                        <td>{{ $membership->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($membership->customers->count() > 0)
            <h5 class="mt-4">{{ trans_common('customers') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('customer_id') }}</th>
                            <th>{{ trans_common('name') }}</th>
                            <th>{{ trans_common('mobile') }}</th>
                            <th>{{ trans_common('joined_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($membership->customers as $customer)
                            <tr>
                                <td>{{ $customer->customer_id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->mobile ?? '-' }}</td>
                                <td>{{ $customer->pivot->joined_at ? $customer->pivot->joined_at->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-3">
            @can('customer.edit')
                <a href="{{ route('admin.memberships.edit', $membership) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.memberships.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
