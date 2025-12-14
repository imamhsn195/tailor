@extends('adminlte::page')

@section('title', trans_common('factory_reports'))

@section('content_header')
    <h1>{{ trans_common('factory_reports') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('factory_reports') }}" theme="primary" icon="fas fa-industry">
        <form method="GET" action="{{ route('admin.reports.factory') }}" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label>{{ trans_common('branch') }}</label>
                    <select name="branch_id" class="form-control">
                        <option value="">{{ trans_common('all') }} {{ trans_common('branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>{{ trans_common('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label>{{ trans_common('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> {{ trans_common('filter') }}
                    </button>
                </div>
            </div>
        </form>

        @if(isset($summary))
            <div class="row mb-3">
                <div class="col-md-4">
                    <x-adminlte-info-box title="{{ trans_common('total_productions') }}" 
                                         text="{{ number_format($summary['total'] ?? 0) }}" 
                                         icon="fas fa-industry" 
                                         theme="info"/>
                </div>
                <div class="col-md-4">
                    <x-adminlte-info-box title="{{ trans_common('completed') }}" 
                                         text="{{ number_format($summary['completed'] ?? 0) }}" 
                                         icon="fas fa-check-circle" 
                                         theme="success"/>
                </div>
                <div class="col-md-4">
                    <x-adminlte-info-box title="{{ trans_common('in_progress') }}" 
                                         text="{{ number_format($summary['in_progress'] ?? 0) }}" 
                                         icon="fas fa-spinner" 
                                         theme="warning"/>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('production_id') }}</th>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('product') }}</th>
                        <th>{{ trans_common('quantity') }}</th>
                        <th>{{ trans_common('status') }}</th>
                        <th>{{ trans_common('date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productions as $production)
                        <tr>
                            <td>{{ $production->id }}</td>
                            <td>{{ $production->branch->name ?? '-' }}</td>
                            <td>{{ $production->product->name ?? '-' }}</td>
                            <td>{{ number_format($production->quantity ?? 0) }}</td>
                            <td>
                                <span class="badge badge-{{ $production->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($production->status ?? '-') }}
                                </span>
                            </td>
                            <td>{{ $production->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $productions->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
