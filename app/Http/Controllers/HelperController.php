<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Storage;
class HelperController extends Controller
{

	public function stringGetLine(&$content){
		$line = "";
		$contentLength = strlen($content);
		for($i = 0; $i < $contentLength; $i++){
			$line .= $content[$i];
			if($content[$i] == "\n")
				break;
		}
		$lineLength = strlen($line);
		$content = substr($content, $lineLength, $contentLength - $lineLength);
		return $line;
	}

	public function getLineType($line){
		$pos1 = strpos($line, "<");
		$type = substr($line, $pos1 + 1, 3);
		return $type;
	}
	public function getLineStatus($line){
		$pos = strrpos($line, "<");
		$status = substr($line, $pos + 1, 3);
		return $status;
	}

	public function formatId($music){
		$music_id = "";
		for($i = 0; $i < 16-strlen($music); $i++){
			$music_id .= "0";
		}
		$music_id.=$music;
		return $music_id;
	}

	public function getID($file_id){
		$id = "";
		for($i = 0; $i < strlen($file_id); $i++){
			if($file_id[$i] == "0" && strlen($id) == 0)
				continue;
			$id .= $file_id[$i];
		}
		return $id;
	}

	public function getDirIdIfRoot($directory_id, $uuid){
		$local_id = DB::table('devices')->where('uuid', $uuid)->pluck('local_id');
		$directories_id = DB::table('directories_local')->where('local_id', $local_id)->pluck('directories_id');
		return $directories_id;
	}

	public function getTime(){
		$mytime = \Carbon\Carbon::now();
		$time = $mytime->toDateTimeString();
		$format = "";
		for($i = 0; $i < 10; $i++){
			if($time[$i] == "-")
				continue;
			$format .= $time[$i];
		}
		$format .= "-";
		for($i = 11; $i < 16; $i++){
			if($time[$i] == ":")
				continue;
			$format .= $time[$i];
		}
		return $format;
	}

	public function getMusicNameByValue($id, $value){
		if($id == NULL)
			return;
		$local_name = DB::table('musics')->where('id', $id)->first();
		return  $local_name->$value;
	}

	public function getDirNameByValue($id, $value){
		if($id == NULL)
			return;
		$name = DB::table('directories')->where('id', $id)->first();
		return $name->$value;
	}

	public function createLine($music_id, $id){
		$time = $this->getTime();
		$local_name = $this->getMusicNameByValue($id, 'local_filename');
		$display_name = $this->getMusicNameByValue($id, 'display_name');
		$line = "\n".$time." <MP3> <TBD> ".$music_id." ".$local_name." ".$display_name;
		return $line;
	}

	public function createWavLine($wav_id, $message){
		$time = app('App\Http\Controllers\HelperController')->getTime();
		$local_name = DB::table('messages')->where('id', $message)->first()->local_filename;
		$display_name = DB::table('messages')->where('id', $message)->first()->updated_at;
		$line = "\n".$time." <WAV> <TBD> ".$wav_id." ".$local_name." ".$display_name;
		return $line;
	}

	public function createDirLine($directories_id, $id){
		$time = $this->getTime();
		$local_name = $this->getDirNameByValue($id, 'name');
		$display_name = $this->getDirNameByValue($id, 'display_name');
		$line = "\n".$time." <DIR> <OCL> ".$directories_id." ".$local_name." ".$display_name;
		return $line;
	}

	public function createChatLine($chat_id, $id, $user_id){
		// $time = app('App\Http\Controllers\HelperController')->getTime();
		// $local_name = DB::table('users')->where('id', $user_id)->first()->name;
		// $chatbot = DB::table('chats')->where('id', $id)->first()->owner_id;
		// if($chatbot == $user_id)
		// 	$chatbot = DB::table('chats')->where('id', $id)->first()->friend_id;
		// $display_name = DB::table('users')->where('id', $chatbot)->first()->name;
		// $line = "\n".$time." <DIR> <OCL> ".$chat_id." ".$local_name." ".$display_name;
		// return $line;

		$time = app('App\Http\Controllers\HelperController')->getTime();
		$friend_id = DB::table('chats')->where('id', $id)->first()->friend_id;
		if ($friend_id == $user_id){
			$friend_id = DB::table('chats')->where('id', $id)->first()->owner_id;
		}
		$local_name = $friend_id;	
		$user_id= DB::table('devices')->where('uuid', $friend_id)->first()->user_id;
		$display_name = DB::table('users')->where('id', $user_id)->first()->name;
		$line = "\n".$time." <DIR> <TBD> ".$chat_id." ".$local_name." ".$display_name;
		return $line;
	}

	public function removeOCL($userfile, $name){
		$path = storage_path('app/ServerStorage/syncDir/')."temp-".$name;
		\File::put($path, '');
		$fp = fopen($userfile, "r");
		$line = fgets($fp);
		\File::append($path, $line);
		$line = fgets($fp);
		\File::append($path, $line);

		while(!feof($fp)){
			$line = fgets($fp);
			if(strlen($line) < 15)
				continue;
			if(str_contains($line, "<OCL>")){
				continue;
			}
			\File::append($path, $line);
		}
		fclose($fp);
		\File::delete($userfile);
		\File::move($path, $userfile);
	}

	public function syncDir($content, $directory_id, $userfile){
		$directories = DB::table('directories')->when($directory_id, function($query, $directory_id){
			return $query->where('parent_id', $directory_id);
		})->pluck('id');

		$directories = $directories->sort();
		foreach($directories as $directory){
			$directories_id = $this->formatId($directory);
			$directoryNSD = "<DIR> <NSD> ".$directories_id;

			if(!str_contains($content, $directoryNSD)){
				$line = $this->createDirLine($directories_id, $directory);
				\File::append($userfile, $line);
			}
		}
	}

	public function syncMusic($content, $directory_id, $userfile){
		$directory_music = DB::table('musics_directories')->when($directory_id, function($query, $directory_id){
			return $query->where('directory_id', $directory_id);
		})->pluck('music_id');

		$directory_music = $directory_music->sort();
		foreach($directory_music as $music){
			$music_id = $this->formatId($music);
			$musicNSD = "<MP3> <NSD> ".$music_id;

			if(!(str_contains($content, $musicNSD))){
				$line = $this->createLine($music_id, $music);
				\File::append($userfile, $line);
			}
		}
	}

	public function getDeleteDir($content, &$deleteItems, $directory_id, &$items){

		$directories = DB::table('directories')->when($directory_id, function($query, $directory_id){
			return $query->where('parent_id', $directory_id);
		})->pluck('id');

		$linepos = 0;

		while(strlen($content) != 0){
			$line = app('App\Http\Controllers\HelperController')->stringGetLine($content);
			$linepos++;

			if(strlen($line) < 49)
				continue;

			$delete = true;
			$pos = strrpos($line, " ");
			$directory_name = substr($line, 43, $pos -43);


			$type = app('App\Http\Controllers\HelperController')->getLineType($line);
			$status = app('App\Http\Controllers\HelperController')->getLineStatus($line);
			if($status == "OCL" || $status == "TBD")
				continue;
			if($type != "DIR")
				continue;
			foreach($directories as $directory){
				$directory_id = app('App\Http\Controllers\HelperController')->formatId($directory);
				if(str_contains($line, $directory_id)){
					$delete = false;
					break;
				}
			}
			if($delete){
				$items++;
				if($items > 10)
					continue;
				if($items == 11)
					break;
				$deleteItems->push($linepos-1);
			}
		}
	}

	public function getDeleteSong($content, &$deleteItems, $directory_id, &$items){
		$directory_music = DB::table('musics_directories')->when($directory_id, function($query, $directory_id){
			return $query->where('directory_id', $directory_id);
		})->pluck('music_id');
		
		$linepos = 0; 

		while(strlen($content) != 0){
			$line = app('App\Http\Controllers\HelperController')->stringGetLine($content);
			$linepos++;

			if(strlen($line) < 49)
				continue;


			$delete = true;
			$pos = strrpos($line, " ");
			$music_name = substr($line, 43, $pos-43);

			$type = app('App\Http\Controllers\HelperController')->getLineType($line);
			$status = app('App\Http\Controllers\HelperController')->getLineStatus($line);
			if($status == "OCL")
				continue;
			if($type != "MP3")
				continue;

			foreach($directory_music as $music){
				$music_id = app('App\Http\Controllers\HelperController')->formatId($music);
				$name = DB::table('musics')->where('id',$music_id)->first()->display_name;
				if(str_contains($line, $music_id) && str_contains($line, $name)){
					$delete = false;
					break;
				}
			}
			if($delete){
				$items++;
				if($items > 10)
					continue;
				if($items == 11)
					break;
				$deleteItems->push($linepos-1);
			}
		}
	}

	public function sortPlayList($userfile, $name, $id){
		$path = storage_path('app/ServerStorage/syncDir/')."temp-".$name;
		\File::put($path, '');
		$fp = fopen($userfile, "r");
		$line = fgets($fp);
		\File::append($path, $line);
		$line = fgets($fp);
		$line = DB::table('directories')->where('id', $id)->first()->updated_at;
		$line .= "\n";
		\File::append($path, $line);

		$offset = ftell($fp);
		$end = true;
		$dir_exist = false;
		$dirline = "";
		$mp3line = "";
		while(!feof($fp)){
			$line = fgets($fp);
			if(strlen($line) < 15)
				continue;
			if(str_contains($line, "<MP3>")){
				$end = false;
			}
			if(str_contains($line, "<DIR>")){
				$dir_exist = true;
				$dirline = $line;
				\File::append($path, $line);
			}
		}
		if( !str_contains($dirline, "\n") && !$end && $dir_exist){
			\File::append($path, "\n");
		}
		fseek($fp,$offset);

		while(!feof($fp)){
			$line = fgets($fp);
			if(strlen($line) < 15)
				continue;
			if(str_contains($line, "<MP3>")){
				$mp3line = $line;
				\File::append($path, $line);
			}
		}
		$pos = ftell($fp);
		if(str_contains($mp3line, "\n")){
			$truncate = fopen($path, "a+");
			ftruncate($truncate, $pos-1);
			fclose($truncate);
		}
		fclose($fp);
			
		\File::delete($userfile);
		\File::move($path, $userfile);
	}

	public function sortChat($userfile, $name, $id){
		$path = storage_path('app/ServerStorage/syncDir/')."temp-".$name;
		\File::put($path, '');
		$fp = fopen($userfile, "r");
		$line = fgets($fp);
		\File::append($path, $line);
		$line = fgets($fp);
		if (strlen($line) < 21){ //if second line is time, replace with new time
			$line = DB::table('chats')->where('id', $id)->first()->updated_at;
			$line .= "\n";
			\File::append($path, $line);
		}
		else{ //if second line is not time, append time and $line
			$line2 = DB::table('chats')->where('id', $id)->first()->updated_at;
			$line2 .= "\n";
			\File::append($path, $line2);
			\File::append($path, $line);
		}

		while(!feof($fp)){
			$line = fgets($fp);
			if(strlen($line) < 15)
				continue;
			\File::append($path, $line);
		}
		fclose($fp);
		\File::delete($userfile);
		\File::move($path, $userfile);
	}

	public function getShouldDeleteVoiceChat($user_id, $content, $items, $deleteItems){
		$chats = DB::table('chats')->where('owner_id', $user_id)->orWhere('friend_id', $user_id)->pluck('id');
		$linepos = 1;
		while(strlen($content) != 0){
			$line = $this->stringGetLine($content);
			$linepos++;
			if(strlen($line) < 47) 
				continue;
			$status = $this->getLineStatus($line);
			if($status == "TBD")
				continue;
			$delete = true;
			$pos = strrpos($line, " ");
			$chat_name = substr($line, 43, $pos-43);

			foreach($chats as $chat){
				$chat_id = $this->formatId($chat);
				if(str_contains($line, $chat_id)){
					$delete = false;
					break;
				}
			}
			if($delete){
				$items++;
				if($items > 10)
					continue;
				if($items == 11)
					break;
				$deleteItems->push($linepos-1);
			}
		}
	}

	public function getShouldDeleteVoiceMessage($chat_id, $user_id, $content, $items, $deleteItems){
		$messages = DB::table('chats_messages')->where('chat_id', $chat_id)->pluck('message_id');

		$linepos = 1;
		while(strlen($content) != 0){
			$line = $this->stringGetLine($content);
			$linepos++;
			if(strlen($line) < 49)
				continue;
			$status = $this->getLineStatus($line);
			if($status == "TBD")
				continue;
			$delete = true;
			$pos = strrpos($line, " ");
			$wav_name = substr($line, 43, $pos-43);

			foreach($messages as $message){
				$wav_id = $this->formatId($message);
				if(str_contains($line, $wav_id)){
					$delete = false;
					break;
				}
			}
			if($delete){
				$items++;
				if($items > 10)
					continue;
				if($items == 11)
					break;
				$deleteItems->push($linepos-1);
			}
		}
	}

	public function syncChat($user_id, $content, $userfile){
		$chats = DB::table('chats')->where('owner_id', $user_id)->orWhere('friend_id', $user_id)->pluck('id');
		$chats = $chats->sort();
		foreach($chats as $chat){
			$chat_id = app('App\Http\Controllers\HelperController')->formatId($chat);
			$chatNSD = "<NSD> ".$chat_id;
			$chatTBD = "<TBD> ".$chat_id;
			if(!(str_contains($content, $chatNSD) || str_contains($content, $chatTBD))){
				$line = $this->createChatLine($chat_id, $chat, $user_id);
				\File::append($userfile, $line);
			}
		}
	}

	public function syncMessage($chat_id, $content, $userfile){
		$messages = DB::table('chats_messages')->where('chat_id', $chat_id)->pluck('message_id');
		$messages = $messages->sort();
		foreach($messages as $message){
			$wav_id = $this->formatId($message);
			$wavNSD = "<NSD> ".$wav_id;
			$wavTBD = "<TBD> ".$wav_id;
			if(!(str_contains($content, $wavNSD) || str_contains($content, $wavTBD))){
				$line = $this->createWavLine($wav_id, $message);
				\File::append($userfile, $line);
			}
		}
	}


	public function checkUpdate($content, $id, $tableToCheckUpdate){
		$line = app('App\Http\Controllers\HelperController')->stringGetLine($content);
		$line = app('App\Http\Controllers\HelperController')->stringGetLine($content);
		$date = DB::table($tableToCheckUpdate)->where('id', $id)->first()->updated_at;
		$date .= "\n";
		if($line == $date)
			return true;
		else
			return false;
	}

}
