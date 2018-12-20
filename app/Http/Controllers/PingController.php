<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Storage;
use DB;
use Illuminate\Support\Facades\Log;

class PingController extends Controller
{
    //return UTC time
	public function getServerTimeUTC()
	{
		$date = new \DateTime();

		return response()->json([
			'UTC' => $date->getTimestamp(),
			]);
	}

	/*
	parameter:
		[in]: $file: uploaded file used to get file content, compare to server's music items
		[in]: $direcotry_id: used to pluck items in given directory
		[in]: $uuid: know which device needed this function
		
		return: information about the item(s) to be deleted
	*/
	public function getShouldDeleteDirItems(Request $request){
		Log::notice("--------------------------");
		Log::notice("getShouldDeleteDirItems");
		//Log::info($request);
		$uuid = $request->input('uuid');
		$user_id = $request->input('user_id');
		$directory_id = $request->input('directory_id');
		Log::notice("uuid");
		Log::info($uuid );
		Log::notice("directory_id");
		Log::info($directory_id );
		$content = file_get_contents($_FILES['file']['tmp_name']);
		$tableToCheckUpdate = 'directories';
		if($directory_id == 0){
			$directory_id = app('App\Http\Controllers\HelperController')->getDirIdIfRoot($directory_id, $uuid);
		}

		$deleteItems = collect([]);
		$items = 0;

		$upToDate = app('App\Http\Controllers\HelperController')->checkUpdate($content, $directory_id, $tableToCheckUpdate);
		if(!$upToDate){
			app('App\Http\Controllers\HelperController')->getDeleteDir($content, $deleteItems, $directory_id, $items);
			app('App\Http\Controllers\HelperController')->getDeleteSong($content, $deleteItems, $directory_id, $items);
		}

		if($items > 10){
			$progress = "more";
		}
		else
			$progress = "done";

		return response()->json([
			"items:" => $deleteItems->count(),
			"delete:" => $deleteItems,
			"Progress:" => $progress,
			]);
	}

	/*
	parameter:
		[in]: $file: uploaded file, to synchronize this file and store it in server for user to download
		[in]: $direcotry_id: used to pluck items in given directory
		[in]: $uuid: used to store the file with uuid
		
		return: file name to be download and update information
	*/
	public function syncDirInfo(Request $request){
		Log::notice("					syncDirInfo");
		//Log::info($request);
		$uuid = $request->input('uuid');
		$directory_id = (int)$request->input('directory_id');
		$content = file_get_contents($_FILES['file']['tmp_name']);
		//Log::info($content);
		Log::notice("uuid");
		Log::info($uuid );
		Log::notice("directory_id");
		Log::info($directory_id );
		$clientFileName = $request->file('file')->getClientOriginalName();
		$path = $request->file('file')->storeAs('ServerStorage/syncDir', $uuid.'-'.$directory_id.'-'.$clientFileName);
		$userfile = storage_path('app/').$path;
		$filename = $uuid.'-'.$directory_id.'-'.$clientFileName;
		$tableToCheckUpdate = 'directories';

		if($directory_id == 0){
			$directory_id = app('App\Http\Controllers\HelperController')->getDirIdIfRoot($directory_id, $uuid);
		}
		$upToDate = app('App\Http\Controllers\HelperController')->checkUpdate($content, $directory_id, $tableToCheckUpdate);
		if(!$upToDate){
			app('App\Http\Controllers\HelperController')->removeOCL($userfile, $uuid.'-'.$directory_id.'-'.$clientFileName);
			app('App\Http\Controllers\HelperController')->syncDir($content, $directory_id, $userfile);
			app('App\Http\Controllers\HelperController')->syncMusic($content, $directory_id, $userfile);
			app('App\Http\Controllers\HelperController')->sortPlayList($userfile, $uuid.'-'.$directory_id.'-'.$clientFileName, $directory_id);
		}
		else{
			return response()->json([
				"sync filename:" => $filename,
				'update:' => "update not required",
				]);
		}

		return response()->json([
			"sync filename:" => $filename,
			'update:' => "update required",
			]);
	}

	/*
	parameter:
		[in]: $file_id: to determine which file to be download (used in mp3 and wav file)
		[in]: $type: to determine which kind of file to be download(mp3/txt/wav)
		[in]: $name:used in txt file, search for File_info.txt or Voice_Info.txt
		[in]: $direcotry_id: used to search txt file
		[in]: $uuid: used to search txt file
		
		return: file to download
	*/
	public function downloadFile(Request $request){
		Log::notice("					downloadFile");
		Log::info($request);
		$file_id = $request->input('file_id');
		$file_id = (int)$file_id;
		$file = null;
		$filename = null;
		$type = $request->input('type');
		if($type == "mp3" || $type == "MP3"){
			$name = DB::table('musics')->where('id', $file_id)->first()->server_name;
			$file = storage_path('app/ServerStorage/mp3/').$name;
			$filename = DB::table('musics')->where('id', $file_id)->first()->local_filename;
		}
		else if($type == "wav" || $type == "WAV"){
			$name = DB::table('messages')->where('id', $file_id)->first()->server_name;
			$file = storage_path('app/public/wav/').$name;
			$filename = DB::table('messages')->where('id', $file_id)->first()->local_filename;
		}
		else if($type == "txt" || $type == "TXT"){
			$uuid = $request->input('uuid');
			$directory_id = (int)$request->input('directory_id');
			$name = $request->input('name');
			$file = storage_path('app/ServerStorage/syncDir/').$uuid.'-'.$directory_id.'-'.$name;
			$filename = $name;
		}
		if (!$file) {
			return abort(404);
		}
		$headers = ['Content-Type: audio/mp3'];
		return response()->download($file, $filename, $headers);
	}

	/*
	parameter:
		[in]: $file: uploaded wav file, store it in server
		[in]: $chat_id: determine which chat romm the user in
		[in]: $sender_id: determine which user send this wav message
		
		return: weather the file is sucessfully uploaded
	*/
	public function uploadVoiceMessage(Request $request){
		Log::notice("							uploadVoiceMessage");
		Log::info($request);
		$sender_uuid = $request->input('sender_uuid');
		$chat_id = $request->input('chat_id');
		$name = $request->file('file')->getClientOriginalName();
		$request->file('file')->storeAs('public/wav', $name);

		$id = DB::table('messages')->insertGetId([
			'local_filename' => $name,
			'display_name' => $name,
			'server_name' => $name,
			"created_at" =>  \Carbon\Carbon::now(),
			"updated_at" =>  \Carbon\Carbon::now(),
			]);

		$user_id = DB::table('devices')->where('uuid', $sender_uuid)->first()->user_id;

		DB::table('messages_users')->insert([
			'message_id' => $id,
			'user_id' => $user_id,
			]);

		DB::table('chats_messages')->insert([
			'chat_id' => $chat_id,
			'message_id' => $id,
			]);

		$updated_at = DB::table('chats')->where('id', $chat_id)->update(['updated_at'=>\Carbon\Carbon::now()]);

		return response()->json([
			"status:" => "recording uploaded",
			]);
	}

	/*
	parameter:
		[in]: $file: uploaded file used to get file content, compare to server's wav items
		[in]: $chat_id: determine which chat room
		[in]: $user_id: determine which user
		
		return: information about the item(s) to be deleted
	*/
	public function getShouldDeleteVoiceMessageItems(Request $request){
		Log::notice("					getShouldDeleteVoiceMessageItems");
		Log::info($request);
		$uuid = $request->input('uuid');
		$content = file_get_contents($_FILES['file']['tmp_name']);
		$user_id = $request->input('user_id');
		$chat_id = (int)app('App\Http\Controllers\HelperController')->stringGetLine($content);
		
		$deleteItems = collect([]);

		$items = 0;

		if($chat_id == 0){
			app('App\Http\Controllers\HelperController')->getShouldDeleteVoiceChat($user_id, $content, $items, $deleteItems);
		}
		else{
			app('App\Http\Controllers\HelperController')->getShouldDeleteVoiceMessage($chat_id, $user_id, $content, $items, $deleteItems);
		}

		if($items > 10){
			$progress = "more";
		}
		else
			$progress = "done";

		return response()->json([
			"items:" => $deleteItems->count(),
			"delete:" => $deleteItems,
			"Progress:" => $progress,
			]);
	}

	/*
	synchronize voice messeage of the device
	parameter:
		[in]: $file: uploaded file, to synchronize this file and store it in server for user to download
		[in]: $chat_it: used to pluck items in given chat room
		[in]: $uuid: used to store the file with uuid
		
		return: file name to be download and update information
	*/
	public function syncVoiceMessage(Request $request){
		Log::notice("					syncVoiceMessage");
		Log::info($request);
		$uuid = $request->input('uuid');
		$user_id = DB::table('devices')->where('uuid', $uuid)->first()->user_id;
		$content = file_get_contents($_FILES['file']['tmp_name']);
		$content2 = file_get_contents($_FILES['file']['tmp_name']);
		$chat_id = (int)app('App\Http\Controllers\HelperController')->stringGetLine($content2);
		Log::info($content);
		$clientFileName = "Voice_Info.TXT"; 
		$filename = $uuid.'-'.$chat_id.'-'.$clientFileName;
		$path = $request->file('file')->storeAs('ServerStorage/syncDir', $filename);
		$userfile = storage_path('app/').$path;
		$tableToCheckUpdate = 'chats';

		$upToDate = app('App\Http\Controllers\HelperController')->checkUpdate($content, $chat_id, $tableToCheckUpdate);
		if(!$upToDate){
			app('App\Http\Controllers\HelperController')->removeOCL($userfile, $uuid.'-'.$chat_id.'-'.$clientFileName);
			if($chat_id == 0){
				app('App\Http\Controllers\HelperController')->syncChat($user_id, $content, $userfile);
			}
			else{
				app('App\Http\Controllers\HelperController')->syncMessage($chat_id, $content, $userfile);
			}
			app('App\Http\Controllers\HelperController')->sortChat($userfile, $uuid.'-'.$chat_id.'-'.$clientFileName, $chat_id);

			return response()->json([
			"sync filename:" => $filename,
			'update:' => "update required",
			]);

		}
		else{

		return response()->json([
			"sync filename:" => $filename,
			'update:' => "update not required",
			]);
		}

	}

	/*
	parameter:
		[in]: $owner_id: user who create this chat room
		[in]: $friend_id: invited user in this chat room
		[in]: $uuid:/

		return: wheather the chat room is created or not
	*/
	public function createChat(Request $request){
		Log::notice("					createChat");
		Log::info($request);
		$owner_id = $request->input('user_id');
		$friend_id = $request->input('receiver_user_id');

		$case3 = DB::table('users')->where('id', $owner_id)->get();
		$case4 = DB::table('users')->where('id', $friend_id)->get();

		if($case3->count() == 0 || $case4->count() == 0)
			return response()->json([
				"status:" => "no_user",
				]);
		
		$case1 = DB::table('chats')->where('owner_id', $owner_id)->Where('friend_id', $friend_id)->get();
		$case2 = DB::table('chats')->where('owner_id', $friend_id)->where('friend_id', $owner_id)->get();

		if($case1->count() == 1 || $case2->count() == 1)
			return response()->json([
				"status:" => "exist",
				]);

		$chat_id = DB::table('chats')->insertGetId([
			'owner_id' => $owner_id,
			'friend_id' => $friend_id,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
			]);

		DB::table('chats')->where('id',0)->update([
                "updated_at" =>  \Carbon\Carbon::now(),
            ]);

		$created_at = DB::table('chats')->where('id', $chat_id)->first()->created_at;
		$updated_at = DB::table('chats')->where('id', $chat_id)->first()->updated_at;

		$updated_at = DB::table('chats')->where('id', 0)->update(['updated_at'=>\Carbon\Carbon::now()]);

		return response()->json([
			"status:" => "created",
			"chat_id" => $chat_id,
			'created_at' => $created_at,
			'updated_at' => $updated_at,
			]);
	}

	/*
	parameter:
		[in]: $delete_id: which chat room to delete

		return: weather the chat room is delete
	*/
	public function deleteChat(Request $request){
		Log::notice("					deleteChat");
		Log::info($request);

		$delete_id = $request->input('delete_id');
		
		DB::table('chats')->where('id', $delete_id)->delete();
		$message_id = DB::table('chats_messages')->where('chat_id', $delete_id)->pluck('message_id');
		DB::table('chats_messages')->where('chat_id', $delete_id)->delete();
		if(count($message_id)>0){
			DB::table('messages')->where('id', $message_id)->delete();
		}

		DB::table('chats')->where('id',0)->update([
                "updated_at" =>  \Carbon\Carbon::now(),
            ]);

		return response()->json([
			"status:" => "deleted",
			]);
	}

	public function createDirectory(Request $request){
		$name = $request->input('name');
		$display_name = $request->input('display_name');
		$parent_id= $request->input('parent_id');
		
		
		$directories_id = DB::table('directories')->insertGetId([
			'name' => $name,
			'display_name' => $display_name,
			'parent_id' => $parent_id,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
			]);

		$created_at = DB::table('directories')->where('id', $directories_id)->first()->created_at;
		$updated_at = DB::table('directories')->where('id', $directories_id)->first()->updated_at;

		return response()->json([
			"status:" => "directory created",
			"device_id" => $directories_id,
			'created_at' => $created_at,
			'updated_at' => $updated_at,
			]);
	}

	public function addMusicToDirectory(Request $request){
		$music_id = $request->input('music_id');
		$directory_id = $request->input('directory_id');

		DB::table('musics_directories')->insert([
			'music_id' => $music_id,
			'directory_id' => $directory_id,
			]);
		
		return response()->json([
			"status:" => "music added to directory",
			"music_id" => $music_id,
			"directory_id" => $directory_id,
			]);
	}

	public function uploadMusic(Request $request){
		$name = $request->file('file')->getClientOriginalName();
		$local_filename= $request->input('local_filename');
		$is_auto_download = $request->input('is_auto_download');
		$local_id = $request->input('local_id');
		$request->file('file')->storeAs('ServerStorage/mp3', $name);

		$music_id = DB::table('musics')->insertGetId([
			'local_filename' => $local_filename,
			'display_name' => $name,
			'server_name' => $name,
			'is_auto_download' => $is_auto_download,
			"created_at" =>  \Carbon\Carbon::now(),
			"updated_at" =>  \Carbon\Carbon::now(),
			]);

		DB::table('music_local')->insert([
			'music_id' => $music_id,
			'local_id' => $local_id,
			]);

		$created_at = DB::table('musics')->where('id', $music_id)->first()->created_at;
		$updated_at = DB::table('musics')->where('id', $music_id)->first()->updated_at;

		return response()->json([
			"status:" => "music uploaded",
			"music_id" => $music_id,
			'created_at' => $created_at,
			'updated_at' => $updated_at,
			]);
	}

	public function deleteVoiceMessage(Request $request){
		Log::notice("							deleteVoiceMessage");
		Log::info($request);
		$message_id = $request->input('message_id');

		$filename = DB::table('messages')->where('id',$message_id)->first()->local_filename;

		DB::table('messages')->where('id',$message_id)->delete();
		DB::table('chats_messages')->where('message_id',$message_id)->delete();
		DB::table('messages_users')->where('message_id',$message_id)->delete();

		$path = "public/wav/".$filename;
		Storage::delete($path);
		
		return response()->json([
			"status:" => "Voice message deleted",
			]);
	}
}





















