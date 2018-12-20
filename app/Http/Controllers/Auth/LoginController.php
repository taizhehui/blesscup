<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }

    public function authenticate(Request $request) {
        $validator = $this->validateLoginData($request);

        if (!$validator->fails()) {
            if ($request->is('cms/*')) {
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password]))
                    return redirect('/cms/user');
                else
                    return redirect()->back()->withErrors([__('cms_login.unauthorized_access')]);
            } else {
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->rememberMe === "on"))
                    return redirect()->back();
                else
                    return redirect()->back()->withErrors([__('login.unauthorized_access')], 'login');
            }
        } else {
            return redirect()->back()->withErrors($validator);
        }
    }

    private function validateLoginData($request) {
        return Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);
    }

    public function cmslogout(Request $request) {
        Auth::logout();
        return redirect('/cms');
    }
}
