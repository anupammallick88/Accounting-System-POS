@extends('layouts.auth2')

@section('title', __('lang_v1.reset_password'))

@section('content')
<div class="login-form col-md-12 col-xs-12 right-col-content">
    <form  method="POST" action="{{ route('password.email') }}">
        {{ csrf_field() }}
         <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="@lang('lang_v1.email_address')">
            <span class="fa fa-envelope form-control-feedback"></span>
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
        <br>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block btn-flat">
                @lang('lang_v1.send_password_reset_link')
            </button>
        </div>
    </form>
</div>
@endsection
