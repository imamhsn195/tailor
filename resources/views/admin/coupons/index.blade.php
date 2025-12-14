@extends('adminlte::page')

@section('title', trans_common('coupons'))

@section('content_header')
    <h1>{{ trans_common('coupons') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('coupons') }}" theme="primary" icon="fas fa-ticket-alt">
        <x-search-form 
            :fields="[
                [
                    'name' => 'search',
                    'type' => 'text',
                    'placeholder' => 'Search by code or name...',
                    'value' => request('search'),
                    'col' => 3
                ],
                [
                    'name' => 'type',
                    'type' => 'select',
                    'defaultOption' => ['value' => '', 'label' => trans_common('all') . ' ' . trans_common('type')],
                    'options' => collect(\App\Enums\CouponType::cases())->mapWithKeys(fn($type) => [$type->value => $type->label()])->toArray(),
                    'value' => request('type'),
                    'col' => 2
                ],
                [
                    'name' => 'status',
                    'type' => 'select',
                    'defaultOption' => ['value' => '', 'label' => trans_common('all') . ' ' . trans_common('status')],
                    'options' => [
                        'active' => trans_common('active'),
                        'inactive' => trans_common('inactive')
                    ],
                    'value' => request('status'),
                    'col' => 2
                ]
            ]"
            :url="route('admin.coupons.index')"
        />
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('coupon_code') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('type') }}</th>
                        <th>{{ trans_common('value') }}</th>
                        <th>{{ trans_common('minimum_amount') }}</th>
                        <th>{{ trans_common('usage_limit') }}</th>
                        <th>{{ trans_common('used_count') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td><code>{{ $coupon->code }}</code></td>
                            <td>{{ $coupon->name }}</td>
                            <td>{{ $coupon->type->label() }}</td>
                            <td>
                                @if($coupon->type->value === 'percentage')
                                    {{ $coupon->value }}%
                                @else
                                    {{ number_format($coupon->value, 2) }}
                                @endif
                            </td>
                            <td>{{ $coupon->minimum_amount ? number_format($coupon->minimum_amount, 2) : '-' }}</td>
                            <td>{{ $coupon->usage_limit ?? trans_common('unlimited') }}</td>
                            <td>{{ $coupon->used_count }}</td>
                            <td>
                                @if($coupon->is_active)
                                    <span class="badge badge-success">{{ trans_common('active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('customer.view')
                                        <a href="{{ route('admin.coupons.show', $coupon) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('customer.edit')
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('customer.delete')
                                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('{{ trans_common('are_you_sure') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="{{ trans_common('delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $coupons->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('customer.create')
            <div class="mt-3">
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('coupon') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop
