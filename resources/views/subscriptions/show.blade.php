@extends('adminlte::page')

@section('title', 'Subscribe to ' . $plan->name)

@section('content_header')
    <h1>Subscribe to {{ $plan->name }}</h1>
@stop

@section('content')
    <x-adminlte-card title="{{ $plan->name }}" theme="primary" icon="fas fa-credit-card">
        <div class="row">
            <div class="col-md-6">
                <h4>Plan Details</h4>
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Plan Name</th>
                        <td>{{ $plan->name }}</td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td><strong>{{ currency_format($plan->price) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Billing Cycle</th>
                        <td>{{ $plan->billing_cycle }}</td>
                    </tr>
                    @if($plan->features)
                        <tr>
                            <th>Features</th>
                            <td>
                                <ul>
                                    @foreach(json_decode($plan->features, true) ?? [] as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6">
                <h4>Payment Information</h4>
                <form action="{{ route('subscriptions.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                    <div class="form-group">
                        <label>Payment Gateway</label>
                        <select name="gateway" class="form-control" required>
                            <option value="stripe">Stripe</option>
                            <option value="paddle">Paddle</option>
                            <option value="sslcommerz">SSLCommerz</option>
                            <option value="aamarpay">Aamarpay</option>
                            <option value="shurjopay">Shurjopay</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tenant Name</label>
                        <input type="text" name="tenant_name" class="form-control" required placeholder="Your company name">
                    </div>

                    <div class="form-group">
                        <label>Domain</label>
                        <input type="text" name="domain" class="form-control" required placeholder="yourdomain.com">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-credit-card"></i> Proceed to Payment
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Plans
            </a>
        </div>
    </x-adminlte-card>
@stop
