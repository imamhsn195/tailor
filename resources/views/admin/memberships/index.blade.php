@extends('adminlte::page')

@section('title', trans_common('memberships'))

@section('content_header')
    <h1>{{ trans_common('memberships') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('memberships') }}" theme="primary" icon="fas fa-id-card">
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
                    'options' => collect(\App\Enums\MembershipType::cases())->mapWithKeys(fn($type) => [$type->value => $type->label()])->toArray(),
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
            :url="route('admin.memberships.index')"
        />
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('id') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('type') }}</th>
                        <th>{{ trans_common('discount_percentage') }}</th>
                        <th>{{ trans_common('customers') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($memberships as $membership)
                        <tr>
                            <td>{{ $membership->id }}</td>
                            <td>{{ $membership->name }}</td>
                            <td>{{ $membership->type->label() }}</td>
                            <td>{{ $membership->discount_percentage }}%</td>
                            <td>
                                <span class="badge badge-info">{{ $membership->customers_count }}</span>
                            </td>
                            <td>
                                @if($membership->is_active)
                                    <span class="badge badge-success">{{ trans_common('active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('customer.view')
                                        <a href="{{ route('admin.memberships.show', $membership) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="{{ trans_common('view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('customer.edit')
                                        <a href="{{ route('admin.memberships.edit', $membership) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="{{ trans_common('edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('customer.delete')
                                        <form action="{{ route('admin.memberships.destroy', $membership) }}" 
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
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $memberships->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        @can('customer.create')
            <div class="mt-3">
                <a href="{{ route('admin.memberships.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }} {{ trans_common('membership') }}
                </a>
            </div>
        @endcan
    </x-adminlte-card>
@stop
