@extends('adminlte::page')

@section('title', 'Subscription Plans')

@section('content_header')
    <h1>Subscription Plans</h1>
@stop

@section('content')
    <x-adminlte-card title="Choose Your Plan" theme="primary" icon="fas fa-credit-card">
        <div class="row">
            @foreach($plans as $plan)
                <div class="col-md-4">
                    <x-adminlte-card title="{{ $plan->name }}" theme="info" icon="fas fa-star">
                        <div class="text-center mb-3">
                            <h2>{{ $plan->formatted_price }}</h2>
                            <p class="text-muted">{{ $plan->billing_cycle }}</p>
                        </div>
                        
                        @if($plan->features)
                            <ul class="list-unstyled">
                                @foreach(json_decode($plan->features, true) ?? [] as $feature)
                                    <li><i class="fas fa-check text-success"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <a href="{{ route('subscriptions.show', $plan) }}" class="btn btn-primary btn-block">
                            Select Plan
                        </a>
                    </x-adminlte-card>
                </div>
            @endforeach
        </div>
    </x-adminlte-card>
@stop
