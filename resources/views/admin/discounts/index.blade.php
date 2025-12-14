@extends('adminlte::page')

@section('title', trans_common('discounts'))

@section('content_header')
    <h1>{{ trans_common('discounts') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('discounts') }}" theme="primary" icon="fas fa-percent">
        <x-search-form 
            :fields="[
                [
                    'name' => 'search',
                    'type' => 'text',
                    'placeholder' => 'Search by name...',
                    'value' => request('search'),
                    'col' => 3
                ],
                [
                    'name' => 'type',
                    'type' => 'select',
                    'defaultOption' => ['value' => '', 'label' => trans_common('all') . ' ' . trans_common('type')],
                    'options' => collect(\App\Enums\DiscountType::cases())->mapWithKeys(fn($type) => [$type->value => $type->label()])->toArray(),
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
            :url="route('admin.discounts.index')"
        />
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('id') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('type') }}</th>
                        <th>{{ trans_common('value') }}</th>
                        <th>{{ trans_common('applicable_to') }}</th>
                        <th>{{ trans_common('start_date') }}</th>
                        <th>{{ trans_common('end_date') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $discount)
                        <tr>
                            <td>{{ $discount->id }}</td>
                            <td>{{ $discount->name }}</td>
                            <td>{{ $discount->type->label() }}</td>
                            <td>
                                @if($discount->type->value === 'percentage')
                                    {{ $discount->value }}%
                                @else
                                    {{ number_format($discount->value, 2) }}
                                @endif
                            </td>
                            <td>
                                @if($discount->customer)
                                    {{ trans_common('customer') }}: {{ $discount->customer->name }}
                                @elseif($discount->membership)
                                    {{ trans_common('membership') }}: {{ $discount->membership->name }}
                                @elseif($discount->product)
                                    {{ trans_common('product') }}: {{ $discount->product->name }}
                                @else
                                    {{ $discount->applicable_to ?? '-' }}
                                @endif
                            </td>
                            <td>{{ $discount->start_date ? $discount->start_date->format('Y-m-d') : '-' }}</td>
                            <td>{{ $discount->end_date ? $discount->end_date->format('Y-m-d') : '-' }}</td>
                            <td>
                                @if($discount->is_active)
                                    <span class="badge badge-success">{{ trans_common('active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('customer.view')
                                        <a href="{{ route('admin.discounts.show', $discount) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('customer.edit')
                                        <a href="{{ route('admin.discounts.edit', $discount) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('customer.delete')
                                        <form action="{{ route('admin.discounts.destroy', $discount) }}" 
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
            {{ $discounts->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('customer.create')
            <div class="mt-3">
                <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('discount') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop
