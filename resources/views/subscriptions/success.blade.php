@extends('adminlte::page')

@section('title', 'Subscription Successful')

@section('content_header')
    <h1>Subscription Successful</h1>
@stop

@section('content')
    <x-adminlte-card title="Thank You!" theme="success" icon="fas fa-check-circle">
        <div class="text-center">
            <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
            <h3>Your subscription has been activated successfully!</h3>
            @if(isset($subscription))
                <p>Subscription ID: <strong>{{ $subscription->id }}</strong></p>
                <p>Plan: <strong>{{ $subscription->plan->name ?? 'N/A' }}</strong></p>
                <p>Expires At: <strong>{{ $subscription->expires_at ? $subscription->expires_at->format('Y-m-d H:i:s') : 'N/A' }}</strong></p>
            @endif
            <p class="mt-4">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login to Your Account
                </a>
            </p>
        </div>
    </x-adminlte-card>
@stop
