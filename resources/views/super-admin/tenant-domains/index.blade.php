@extends('adminlte::page')

@section('title', 'Tenant Domains')

@section('content_header')
    <h1>Domains for {{ $tenant->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Custom Domains</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addDomainModal">
                    <i class="fas fa-plus"></i> Add Domain
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Primary</th>
                        <th>Verified</th>
                        <th>Verified At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($domains as $domain)
                        <tr>
                            <td><code>{{ $domain->domain }}</code></td>
                            <td>
                                @if($domain->is_primary)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if($domain->is_verified)
                                    <span class="badge badge-success">Verified</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ $domain->verified_at ? $domain->verified_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>
                                @if(!$domain->is_primary)
                                    <form action="{{ route('super-admin.tenant-domains.set-primary', [$tenant, $domain]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info">Set Primary</button>
                                    </form>
                                @endif
                                @if(!$domain->is_verified)
                                    <form action="{{ route('super-admin.tenant-domains.verify', [$tenant, $domain]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Verify</button>
                                    </form>
                                @endif
                                <form action="{{ route('super-admin.tenant-domains.destroy', [$tenant, $domain]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No domains found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Domain Modal -->
    <div class="modal fade" id="addDomainModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('super-admin.tenant-domains.store', $tenant) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Add Custom Domain</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="domain">Domain</label>
                            <input type="text" class="form-control" id="domain" name="domain" placeholder="example.com" required>
                            <small class="form-text text-muted">Enter the custom domain (without http:// or https://)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Domain</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

