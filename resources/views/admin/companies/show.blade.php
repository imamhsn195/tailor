@extends('adminlte::page')

@section('title', trans_common('view') . ' ' . trans_common('companies'))

@section('content_header')
    <h1>{{ trans_common('view') }} {{ trans_common('companies') }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $company->name }}" theme="primary" icon="fas fa-building">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('name') }}</th>
                        <td>{{ $company->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('branch_name') }}</th>
                        <td>{{ $company->branch_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('email') }}</th>
                        <td>{{ $company->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('phone') }}</th>
                        <td>{{ $company->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('mobile') }}</th>
                        <td>{{ $company->mobile ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('website') }}</th>
                        <td>{{ $company->website ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ trans_common('address') }}</th>
                        <td>{{ $company->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('invoice_name') }}</th>
                        <td>{{ $company->invoice_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('company_registration_no') }}</th>
                        <td>{{ $company->company_registration_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('company_tin_no') }}</th>
                        <td>{{ $company->company_tin_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('e_bin') }}</th>
                        <td>{{ $company->e_bin ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans_common('bin') }}</th>
                        <td>{{ $company->bin ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($company->branches->count() > 0)
            <h5 class="mt-4">{{ trans_common('branches') }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans_common('branch_id') }}</th>
                            <th>{{ trans_common('name') }}</th>
                            <th>{{ trans_common('status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($company->branches as $branch)
                            <tr>
                                <td>{{ $branch->branch_id }}</td>
                                <td>{{ $branch->name }}</td>
                                <td>
                                    @if($branch->is_active)
                                        <span class="badge badge-success">{{ trans_common('active') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ trans_common('inactive') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-3">
            @can('company.edit')
                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ trans_common('edit') }}
                </a>
            @endcan
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans_common('back') }}
            </a>
        </div>
    </x-adminlte-card>
@stop

