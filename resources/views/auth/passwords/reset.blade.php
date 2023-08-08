@extends('layouts.auth2')

@section('title', __('lang_v1.reset_password'))

@section('content')
<div class="login-form col-md-12 col-xs-12 right-col-content">
    <form method="POST" action="{{ route('password.request') }}">
        {{ csrf_field() }}

            <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
            <input id="email" type="email" class="form-control" name="email" value="{{ $email ?? old('email') }}" required autofocus placeholder="@lang('lang_v1.email_address')">
            <span class="fa fa-envelope form-control-feedback"></span>
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
            <input id="password" type="password" class="form-control" name="password"
             required placeholder="@lang('lang_v1.password')">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </div>
        <div class="form-group has-feedback {{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
            <input id="password" type="password" class="form-control" name="password_confirmation" required placeholder="@lang('business.confirm_password')">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            @if ($errors->has('password_confirmation'))
                <span class="help-block">
                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                </span>
            @endif
        </div>
        <br>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block btn-flat">@lang('lang_v1.reset_password')</button>
            <!-- /.col -->
        </div>
    </form>
</div>
@endsection
