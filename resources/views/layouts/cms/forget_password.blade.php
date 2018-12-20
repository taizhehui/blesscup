@extends('layouts.cms.login_master')

@section('script')
    <script src="{{ asset('js/cms/forget_password.js') }}"></script>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-auto title">
            {{ __('cms_login.title') }}
        </div>
    </div>
    @include('layouts.cms.error_list', ['errors' => $errors])
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form method="POST" action="{{ route('password.email') }}">
                {{ csrf_field() }}
                <h4>
                    {{ __('cms_forget_password.forget_password') }}
                </h4>
                <div>
                    {{ __('cms_forget_password.input_email') }}
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="{{ __('cms_forget_password.email_placeholder') }}" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block" id="submit-btn">{{ __('cms_forget_password.send_btn') }}</button>
            </form>
        </div>
    </div>
    <div class="row justify-content-center please-wait-container">
        <div class="col-auto">{{ __('cms_master.please_wait') }}</div>
    </div>
    <div class="row justify-content-center">
        <div class="col-auto">{{ \Illuminate\Support\Facades\Session::get('status') }}</div>
    </div>
    <div class="row justify-content-center">
        <div class="col-auto">
            <br>
            <a href="/cms" class="btn btn-primary">{{ __('cms_master.back') }}</a>
        </div>
    </div>
@endsection