@extends('adminlte::page')

@section('title', trans_common('gift_vouchers'))

@section('content_header')
    <h1>{{ trans_common('gift_vouchers') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('gift_vouchers') }}" theme="primary" icon="fas fa-gift">
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
                    'name' => 'status',
                    'type' => 'select',
                    'defaultOption' => ['value' => '', 'label' => trans_common('all') . ' ' . trans_common('status')],
                    'options' => collect(\App\Enums\GiftVoucherStatus::cases())->mapWithKeys(fn($status) => [$status->value => $status->label()])->toArray(),
                    'value' => request('status'),
                    'col' => 2
                ],
                [
                    'name' => 'customer_id',
                    'type' => 'select',
                    'defaultOption' => ['value' => '', 'label' => trans_common('all') . ' ' . trans_common('customers')],
                    'options' => $customers->mapWithKeys(fn($customer) => [$customer->id => $customer->name . ' (' . $customer->customer_id . ')'])->toArray(),
                    'value' => request('customer_id'),
                    'col' => 2
                ]
            ]"
            :url="route('admin.gift-vouchers.index')"
        />
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('voucher_code') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('amount') }}</th>
                        <th>{{ trans_common('customer') }}</th>
                        <th>{{ trans_common('issued_date') }}</th>
                        <th>{{ trans_common('expiry_date') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($giftVouchers as $giftVoucher)
                        <tr>
                            <td><code>{{ $giftVoucher->voucher_code }}</code></td>
                            <td>{{ $giftVoucher->name ?? '-' }}</td>
                            <td>{{ number_format($giftVoucher->amount, 2) }}</td>
                            <td>{{ $giftVoucher->customer ? $giftVoucher->customer->name : '-' }}</td>
                            <td>{{ $giftVoucher->issued_date->format('Y-m-d') }}</td>
                            <td>{{ $giftVoucher->expiry_date ? $giftVoucher->expiry_date->format('Y-m-d') : '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $giftVoucher->status->badgeColor() }}">
                                    {{ $giftVoucher->status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('customer.view')
                                        <a href="{{ route('admin.gift-vouchers.show', $giftVoucher) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('customer.edit')
                                        <a href="{{ route('admin.gift-vouchers.edit', $giftVoucher) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('customer.delete')
                                        <form action="{{ route('admin.gift-vouchers.destroy', $giftVoucher) }}" 
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
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $giftVouchers->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('customer.create')
            <div class="mt-3">
                <a href="{{ route('admin.gift-vouchers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('gift_voucher') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop
