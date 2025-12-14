@extends('adminlte::page')

@section('title', 'Subscription Failed')

@section('content_header')
    <h1>Subscription Failed</h1>
@stop

@section('content')
    <x-adminlte-card title="Payment Failed" theme="danger" icon="fas fa-times-circle">
        <div class="text-center">
            <i class="fas fa-times-circle fa-5x text-danger mb-3"></i>
            <h3>Your subscription payment could not be processed</h3>
            @if(isset($message))
                <p class="text-danger">{{ $message }}</p>
            @endif
            <p class="mt-4">
                <a href="{{ route('subscriptions.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Try Again
                </a>
            </p>
        </div>
    </x-adminlte-card>
@stop
