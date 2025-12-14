@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('customer'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('customer') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $customer->name }}" theme="primary" icon="fas fa-user">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('customer_id') }}</th>
                        <td>{{ $customer->customer_id }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('name') }}</th>
                        <td>{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('mobile') }}</th>
                        <td>{{ $customer->mobile ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('phone') }}</th>
                        <td>{{ $customer->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('email') }}</th>
                        <td>{{ $customer->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('discount_percentage') }}</th>
                        <td>{{ $customer->discount_percentage }}%</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('address') }}</th>
                        <td>{{ $customer->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('status') }}</th>
                        <td>
                            @if($customer->is_active)
                                <span class="badge badge-success">{{ trans_common('active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('comments') }}</th>
                        <td>{{ $customer->comments ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('created_at') }}</th>
                        <td>{{ $customer->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($customer->memberships->count() > 0)
            <h5 class="mt-4">{{ trans_common('memberships') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('name') }}</th>
                            <th>{{ trans_common('type') }}</th>
                            <th>{{ trans_common('discount_percentage') }}</th>
                            <th>{{ trans_common('joined_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->memberships as $membership)
                            <tr>
                                <td>{{ $membership->name }}</td>
                                <td>{{ $membership->type->label() }}</td>
                                <td>{{ $membership->discount_percentage }}%</td>
                                <td>{{ $membership->pivot->joined_at ? $membership->pivot->joined_at->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($customer->comments()->count() > 0)
            <h5 class="mt-4">{{ trans_common('customer_comments') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('date') }}</th>
                            <th>{{ trans_common('user') }}</th>
                            <th>{{ trans_common('comment') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->comments as $comment)
                            <tr>
                                <td>{{ $comment->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $comment->user->name ?? '-' }}</td>
                                <td>{{ $comment->comment }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @can('customer.edit')
            <div class="mt-4">
                <h5>{{ trans_common('add') }} {{ trans_common('customer_comment') }}</h5>
                <form action="{{ route('admin.customers.comments.store', $customer) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <textarea name="comment" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> {{ trans_common('add') }} {{ trans_common('comment') }}
                    </button>
                </form>
            </div>
        @endcan

        <div class="mt-3">
            @can('customer.edit')
                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
