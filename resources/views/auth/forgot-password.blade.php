@extends('adminlte::auth.passwords.email')

@section('auth_header', trans_common('forgot_password'))

@section('auth_body')
    <p class="login-box-msg">{{ trans_common('forgot_password_message') }}</p>

    <form action="{{ route('password.email') }}" method="post">
        @csrf
        
        {{-- Email field --}}
        <div class="input-group mb-3">
            <input type="email" 
                   name="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" 
                   placeholder="{{ trans_common('email') }}"
                   required 
                   autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">
                    {{ trans_common('send_password_reset_link') }}
                </button>
            </div>
        </div>
    </form>

    <p class="mt-3 mb-1">
        <a href="{{ route('login') }}">{{ trans_common('back_to_login') }}</a>
    </p>
@endsection


