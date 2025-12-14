@extends('adminlte::page')

@section('title', trans_common('hr_reports'))

@section('content_header')
    <h1>{{ trans_common('hr_reports') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ trans_common('hr_reports') }}" theme="primary" icon="fas fa-users-cog">
        <form method="GET" action="{{ route('admin.reports.hr') }}" class="mb-3">
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
                    <x-adminlte-info-box title="{{ trans_common('total_employees') }}" 
                                         text="{{ number_format($summary['total'] ?? 0) }}" 
                                         icon="fas fa-users" 
                                         theme="info"/>
                </div>
                <div class="col-md-4">
                    <x-adminlte-info-box title="{{ trans_common('active_employees') }}" 
                                         text="{{ number_format($summary['active'] ?? 0) }}" 
                                         icon="fas fa-user-check" 
                                         theme="success"/>
                </div>
                <div class="col-md-4">
                    <x-adminlte-info-box title="{{ trans_common('total_salary') }}" 
                                         text="{{ currency_format($summary['total_salary'] ?? 0) }}" 
                                         icon="fas fa-money-bill-wave" 
                                         theme="warning"/>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans_common('employee_id') }}</th>
                        <th>{{ trans_common('name') }}</th>
                        <th>{{ trans_common('branch') }}</th>
                        <th>{{ trans_common('department') }}</th>
                        <th>{{ trans_common('designation') }}</th>
                        <th>{{ trans_common('salary') }}</th>
                        <th>{{ trans_common('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->employee_id ?? $employee->id }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->branch->name ?? '-' }}</td>
                            <td>{{ $employee->department->name ?? '-' }}</td>
                            <td>{{ $employee->designation->name ?? '-' }}</td>
                            <td>{{ currency_format($employee->salary ?? 0) }}</td>
                            <td>
                                <span class="badge badge-{{ $employee->is_active ? 'success' : 'danger' }}">
                                    {{ $employee->is_active ? trans_common('active') : trans_common('inactive') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ trans_common('no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $employees->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop
