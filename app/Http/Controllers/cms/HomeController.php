<?php

namespace App\Http\Controllers\cms;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        return view('layouts.cms.login');
    }

    public function forgetPassword() {
        return view('layouts.cms.forget_password');
    }

    public function register() {
        return view('auth.register');
    }

    public function changePassword(Request $request) {
        $validator = $this->validateChangePasswordData($request);
        if (!$validator->fails()) {
            $user = Auth::user();
            if (Hash::check($request->old_password, $user->password)) {
                $user->update([
                    'password' => Hash::make($request->new_password)
                ]);

                return response()->json();
            } else
                return response()->json(['error' => __('cms_master.invalid_old_pw')], 400);
        } else
            return response()->json(['error' => $validator->errors()->first()], 400);
    }

    private function validateChangePasswordData($request) {
        return Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
            'new_password_confirmation' => 'required'
        ]);
    }
}
