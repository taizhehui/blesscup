<?php

namespace App\Http\Controllers\cms;

use App\User;
use App\Http\Controllers\Controller;
use DB;

class UserListController extends Controller
{
    //join devices, users and locals table, one user may have two cups, each cup has one language
    //filter out admin (user_code != 0)
    public function index() {
        $users = DB::table('devices')                       
                ->join('users','user_id','=','users.id')
                ->where('users.user_code','!=','0')
                ->join('locals','local_id','=', 'locals.id')
                ->select('devices.uuid','locals.name as language','users.*')
                ->get();

        return view('layouts.cms.user', ['users' => $users]);
    }
     
    public function language($local_id) {
        $users = DB::table('devices')
                ->where('local_id',$local_id)                       
                ->join('users','user_id','=','users.id')
                ->where('users.user_code','!=','0')
                ->join('locals','local_id','=', 'locals.id')
                ->select('devices.uuid','locals.name as language','users.*')
                ->get();

        return view('layouts.cms.user', ['users' => $users]);
    }


}
