<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/png" href="/favicon.png" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/datatables.min.css"/>
    <link rel="stylesheet" href=“https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css”/>
    <link href="{{ asset('css/cms/master.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    @yield('style')

</head>
<body>
<div id="app">
    <nav class="navbar navbar-dark bg-dark navbar-expand-md">
        <a class="navbar-brand" href="/cms/user">{{ __('cms_master.title') }}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse collapse_menu" id="navbarNav">
            <nav class="navbar-nav ml-auto">
                <div class="d-md-none d-sm-block">
                    @include('layouts.cms.side_menu_items')
                    <div class="dropdown-divider"></div>
                </div>
                <a class="nav-link" data-toggle="modal" class="disable">{{ __('cms_master.admin') }}{{ Auth::user()->name }} </a>
                <a class="nav-link" data-toggle="modal" href="#changePasswordModal">{{ __('cms_master.change_password') }}</a>
                <form method="POST" action="/cms/logout">
                    {{ csrf_field() }}
                    <button class="text-left navbar-form-btn btn-block btn-outline-dark nav-link">{{ __('cms_master.logout') }}</button>
                </form>
            </nav>
        </div>
    </nav>

    <div class="row no-gutters">
        <div class="col-md-2">
            <nav class="nav flex-column side_menu d-none d-md-block">
                @include('layouts.cms.side_menu_items')
            </nav>
        </div>
        <div class="col-md-10 content_container">
            @yield('content')
        </div>
    </div>

    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cms_master.change_password') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="oldPw" class="col-form-label">{{ __('cms_master.old_password') }}</label>
                    <input type="password" class="form-control" id="oldPw" name="old_password">

                    <label for="newPw" class="col-form-label">{{ __('cms_master.new_password') }}</label>
                    <input type="password" class="form-control" id="newPw" name="new_password" placeholder="{{ __('master.hint_password') }}">

                    <label for="confirmPw" class="col-form-label">{{ __('cms_master.confirm_password') }}</label>
                    <input type="password" class="form-control" id="confirmPw" name="new_password_confirmation" placeholder="{{ __('master.hint_password') }}">
                </div>
                <div class="modal-footer">
                    <p class="password-wait-msg"></p>
                    <div class="password-btn-container">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('cms_master.back') }}</button>
                        <button type="button" class="btn btn-success" id="save-password-btn">{{ __('cms_master.save') }}</button>
                    </div>
                </div>
                <p id="demo"></p>
            </div>
        </div>
    </div>

</div>
</body>
<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="https://cdn.datatables.net/v/bs4/dt-1.10.16/datatables.min.js"></script>
<script src="{{ asset('js/cms/master.js') }}"></script>
@yield('script')
@include('layouts.cms.master-ajax')
</html>
