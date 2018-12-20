<?php

namespace App\Http\Controllers\cms;

use App\User;
use App\Chat;
use App\Device;
use Auth;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatroomController extends Controller
{
    public function index($local_id,$chat_id) {
        $admin_id = Auth::user()->id;
        $admin_uuid = DB::table('devices')->where('user_id',$admin_id)->first()->uuid;

        $chats = DB::table('chats')
                ->where('owner_id',$admin_uuid)
                ->orwhere('friend_id',$admin_uuid)
                ->join('devices','owner_id','=','devices.uuid')
                ->where('devices.local_id','=',$local_id)
        		->join('users','devices.user_id','=','users.id')
                ->distinct()
        		->select('chats.id as chat_id','devices.uuid as uuid','users.name as name','chats.updated_at as date','devices.local_id as local_id')
                ->get();
        if($chat_id==0){ //blesscup is not specified
            $chat_id = $chats[sizeof($chats)-1]->chat_id; //find the most recent one
        }
        $messages = DB::table('chats_messages')
                ->where('chat_id','=',$chat_id)
                ->join('messages','message_id','=','messages.id')
                ->join('messages_users','messages.id','=','messages_users.message_id')
                ->get();

        $row = DB::table('chats')
                ->where('id',$chat_id)
                ->first();
        if($row->owner_id == $admin_id){
            $friend_uuid = $row->friend_id;
        }
        else{
            $friend_uuid = $row->owner_id;
        }
        $friend = DB::table('devices')
                        ->where('uuid',$friend_uuid)
                        ->join('users','user_id','=','users.id')
                        ->select('users.id as user_id','users.name as name','devices.uuid as uuid')
                        ->first();
        $admin = DB::table('users')
                        ->where('id',$admin_id)
                        ->first();

        $blesscup = DB::table('devices') 
                ->where('local_id',$local_id)                       
                ->join('users','user_id','=','users.id')
                ->where('users.user_code','!=','0')
                ->get();

        return view('layouts.cms.chatroom', ['chats' => $chats, 'messages' => $messages, 'chat_id' => $chat_id , 'admin' => $admin, 'friend' => $friend , 'blesscup'=> $blesscup]);
    }

    public function uploadVoiceMessage(Request $request) {
        Log::notice("                   uploadVoiceMessageAdmin");
        Log::info($request);

        $name = $request->name;
        $chat_id = $request->chat_id;

        $request->file('voice_message')->storeAs('public/wav', $name);

        $message_id = DB::table('messages')->insertGetId([
            'local_filename' => $name,
            'display_name' => $name,
            'server_name' => $name,
            "created_at" =>  \Carbon\Carbon::now(),
            "updated_at" =>  \Carbon\Carbon::now(),
            ]);

        DB::table('messages_users')->insert([
            'message_id' => $message_id,
            'user_id' => Auth::user()->id
            ]);

        DB::table('chats_messages')->insert([
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            ]);

        DB::table('chats')->where('id',$chat_id)->update([
            "updated_at" =>  \Carbon\Carbon::now(),
            ]);


        return "ok";



    }
    public function refreshAfterUpload(Request $request) {
        Log::notice("                   refreshAfterUpload");
        Log::info($request);
        return redirect()->back()->with('message', __('cms_chatroom.send_success'));
    }

    public function createChat($uuid){
        Log::notice("                   createChat");
        $admin_id = Auth::user()->id;
        $admin_uuid = DB::table('devices')->where('user_id',$admin_id)->first()->uuid;

        $owner_id = $uuid;
        $friend_id = $admin_uuid;

        $user_id = DB::table('devices')->where('uuid',$uuid)->first()->user_id;

        $case3 = DB::table('users')->where('id', $admin_id)->get();
        $case4 = DB::table('users')->where('id', $user_id)->get();

        if($case3->count() == 0 || $case4->count() == 0)
            return redirect()->back()->with('message', __('cms_chatroom.user_no_exist'));
        
        $case1 = DB::table('chats')->where('owner_id', $owner_id)->where('friend_id', $friend_id)->get();
        $case2 = DB::table('chats')->where('owner_id', $friend_id)->where('friend_id', $owner_id)->get();

        if($case1->count() == 1 || $case2->count() == 1)
            return redirect()->back()->with('message', __('cms_chatroom.chat_exist'));

        $chat_id = DB::table('chats')->insertGetId([
            'owner_id' => $owner_id,
            'friend_id' => $friend_id,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
            ]);

        return redirect()->back()->with('message', __('cms_chatroom.add_success'));
    }

    public function deleteVoiceMessage($id){
        Log::notice("                           deleteVoiceMessage");
        $message_id = $id;

        $filename = DB::table('messages')->where('id',$message_id)->first()->local_filename;

        DB::table('messages')->where('id',$message_id)->delete();
        DB::table('chats_messages')->where('message_id',$message_id)->delete();
        DB::table('messages_users')->where('message_id',$message_id)->delete();

        $path = "public/wav/".$filename;
        Storage::delete($path);
        
        return redirect()->back()->with('message', __('cms_chatroom.delete_success'));
    }
}
