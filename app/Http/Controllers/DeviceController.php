<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
	//register device, generate a random uuid for a devices, return device id
	public function registerDevice(Request $request){
		Log::notice("					registerDevice");
		Log::info($request);
		$device = DB::table('devices');
		$uuid = rand() % 99999999;
		$device->insert([
			'uuid'=> $uuid, 
			'user_id' => 'NULL',
			'local_id' => 0,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
			]);
		// $id = DB::table('devices')->when($uuid, function($query, $uuid){
		// 	return $query->where('uuid', $uuid);
		// })->pluck('id');
		return response()->json([
			"status:" => "device created",
			"uuid:" => $uuid,
			]);
	}

	// //co-relate device and user
	// public function setDeviceUserInfo(Request $request){

	// 	$uuid = $request->input('uuid');
	// 	$name= $request->input('name');
	// 	$email = $request->input('email');
	// 	$password= $request->input('password');
	// 	$gender = $request->input('gender');
	// 	$dob= $request->input('dob');
	// 	$user_code= $request->input('user_code');
	// 	$remember_token= $request->input('remember_token');
	// 	$language= $request->input('language');

	// 	$case1 = DB::table('devices')->where('uuid', $uuid)->get();

	// 	if($case1->count() == 0 )
	// 		return response()->json([
	// 			"status:" => "wrong_uuid",
	// 			]);
		
	// 	$user_id = DB::table('users')->insertGetId([
	// 		'name' => $name,
	// 		'email' => $email,
	// 		'password' => $password,
	// 		'gender' => $gender,
	// 		'dob' => $dob,
	// 		'user_code' => $user_code,
	// 		'remember_token' => $remember_token,
	// 		'created_at' => \Carbon\Carbon::now(),
	// 		'updated_at' => \Carbon\Carbon::now(),
	// 		]);

	// 	$local_id = DB::table('locals')->where('name', $language)->first()->id;

	// 	DB::table('devices')->where('uuid',$uuid)->update([
	// 		'user_id' => $user_id,
	// 		'local_id' => $local_id,
	// 		]);


	// 	$created_at = DB::table('users')->where('id', $user_id)->first()->created_at;
	// 	$updated_at = DB::table('users')->where('id', $user_id)->first()->updated_at;

	// 	return response()->json([
	// 		"status:" => "user created",
	// 		"user_id" => $user_id,
	// 		'created_at' => $created_at,
	// 		'updated_at' => $updated_at,
	// 		]);
	// }

	// //return uuid by specific id provided
	// public function getUUID(Request $request){
	// 	$user_id = $request->input('id');

	// 	$users = DB::table('devices')
	// 	->when($user_id, function ($query, $user_id) {
	// 		return $query->where('id', $user_id);
	// 	});
	// 	$uuid = $users->pluck('uuid');

	// 	return response()->json([
	// 		'uuid' => $uuid,
	// 		]);
	// }

	public function NewUser(Request $request){
		$uuid = $request->input('uuid');
		$name= $request->input('name');
		$language= $request->input('language');

		$case1 = DB::table('devices')->where('uuid', $uuid)->get();

		if($case1->count() == 0 )
			return response()->json([
				"status:" => "wrong_uuid",
				]);
		
		$user_id = DB::table('users')->insertGetId([
			'name' => $name,
			'email' => 'NULL',
			'password' => 'NULL',
			'gender' => 'NULL',
			'dob' => 'NULL',
			'user_code' => 'NULL',
			'remember_token' => 'NULL',
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
			]);

		$local_id = DB::table('locals')->where('name', $language)->first()->id;

		DB::table('devices')->where('uuid',$uuid)->update([
			'user_id' => $user_id,
			'local_id' => $local_id,
			]);

		$created_at = DB::table('users')->where('id', $user_id)->first()->created_at;
		$updated_at = DB::table('users')->where('id', $user_id)->first()->updated_at;

		return response()->json([
			"status:" => "user created",
			"user_id" => $user_id,
			'created_at' => $created_at,
			'updated_at' => $updated_at,
			]);

	}

	public function linkUser (Request $request){
		$uuid = $request->input('uuid');
		$user_id = $request->input('user_id');
		$language = $request->input('language');
		
		$local_id = DB::table('locals')->where('name', $language)->first()->id;

		DB::table('devices')->where('uuid',$uuid)->update([
			'user_id' => $user_id,
			'local_id' => $local_id,
			]);

		return response()->json([
			"status:" => "linked",
			]);
	}
}

