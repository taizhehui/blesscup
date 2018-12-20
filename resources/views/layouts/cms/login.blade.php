@extends('layouts.cms.login_master')

@section('content')
<div class="row justify-content-center">
    <div class="col-auto title">
        {{ __('cms_login.title') }}
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-md-6">
        <form method="POST" action="/cms/login">
            {{ csrf_field() }}
            <div class="form-group">
                <input type="email" class="form-control" placeholder="{{ __('cms_login.email_placeholder') }}" required name="email">
            </div>
            <div>
                <input type="password" class="form-control" placeholder="{{ __('cms_login.password_placeholder') }}" required name="password">
            </div>
            <div class="form-group text-right">
                <a href="/cms/register">{{ __('cms_login.register') }}</a>
                <a href="/cms/forgetPassword">{{ __('cms_login.forget_password') }}</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block">{{ __('cms_login.login_btn') }}</button>
        </form>
    </div>
</div>
@endsection