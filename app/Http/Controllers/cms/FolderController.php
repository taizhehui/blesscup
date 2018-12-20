<?php

namespace App\Http\Controllers\cms;

use App\User;
use App\Directory;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Storage;

class FolderController extends Controller
{

    public function index() {

        $id = 2;
        $directory = Directory::where('parent_id',$id)->get();
        $file= Directory::where('directories.id',$id)                     
                        ->join('musics_directories','id','=','musics_directories.directory_id')
                        ->join('musics','musics_directories.music_id','=', 'musics.id')
                        ->select('musics.local_filename as name','musics.*')
                        ->distinct()
                        ->get();
        return view('layouts.cms.folder', ['directorylist' => $directory], ['filelist' => $file])->with('current_directory',$id);
    }

    public function subfolder($id) {

        $directory = Directory::where('parent_id',$id)->get();
        $file= Directory::where('directories.id',$id)                     
		                ->join('musics_directories','id','=','musics_directories.directory_id')
		                ->join('musics','musics_directories.music_id','=', 'musics.id')
		                ->select('musics.local_filename as name','musics.*')
		                ->distinct()
		                ->get();

        return view('layouts.cms.folder', ['directorylist' => $directory , 'filelist' => $file])->with('current_directory',$id);

    }

    public function deleteDirectory($id) {
        $delete_id = $id;

        $delete_directory = DB::table('directories')->where('id', $delete_id)->first();
        $parent_id = DB::table('directories')->where('id', $delete_id)->first()->parent_id;
        $child_directories_id = DB::table('directories')->where('parent_id', $delete_id)->pluck('id');
        $child_musics_id = DB::table('musics_directories')->where('directory_id', $delete_id)->pluck('music_id');

        //delete everything inside

        for($i =0 ; $i< sizeof($child_directories_id) ; $i++){
           $this->deleteDirectory($child_directories_id[$i]);
        }

        for($i =0 ; $i< sizeof($child_musics_id) ; $i++){
           $this->deleteFile($child_musics_id[$i]);
        }
        
        DB::table('directories')->where('id', $delete_id)->delete();
        DB::table('directories_local')->where('directories_id', $delete_id)->delete();
        DB::table('musics_directories')->where('directory_id', $delete_id)->delete();

        DB::table('directories')->where('id',$parent_id)->update([
                "updated_at" =>  \Carbon\Carbon::now(),
            ]);

        return redirect()->back()->with('message', __('cms_folder.delete_success', ['folder' => $delete_directory->display_name]));
    }

    public function deleteFile($id) {
        $delete_id = $id;

        $delete_music = DB::table('musics')->where('id', $delete_id)->first();
        $delete_directories_id = DB::table('musics_directories')->where('music_id', $delete_id)->first()->directory_id;
        $filename = DB::table('musics')->where('id',$delete_id)->first()->server_name;

        DB::table('musics')->where('id', $delete_id)->delete();
        DB::table('musics_directories')->where('music_id', $delete_id)->delete();
        DB::table('music_local')->where('music_id', $delete_id)->delete();

        DB::table('directories')->where('id',$delete_directories_id)->update([
                "updated_at" =>  \Carbon\Carbon::now(),
            ]);

        $path = "/ServerStorage/mp3/".$filename;
        Storage::delete($path);

        return redirect()->back()->with('message', __('cms_folder.delete_success', ['folder' => $delete_music->display_name]));
    }

    public function editDirectory(Request $request,$id) {
        $validator = $this->validateEditDirectoryData($request);
        if(!$validator->fails()){
            $edit_id = $id;

            foreach ($request->localeData as $localeData) {
                $name = $localeData['name'];
                $displayName = $localeData['display_name'];
            }
            DB::table('directories')->where('id', $edit_id)->update(['name'=>$name]);
            DB::table('directories')->where('id', $edit_id)->update(['display_name'=>$displayName]);
            DB::table('directories')->where('id', $edit_id)->update(['updated_at'=>\Carbon\Carbon::now()]);

            $return_value = $name . " " . $displayName;

            DB::table('directories')->where('id',$id)->update([
                "updated_at" =>  \Carbon\Carbon::now(),
            ]);

            return redirect()->back()->with('message', __('cms_folder.edit_success', ['folder' => $return_value]));
        }
        else{
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
    }

    public function editFile(Request $request,$id) {
        $validator = $this->validateEditFileData($request);
        if(!$validator->fails()){
             $edit_id = $id;
             $edit_directories_id = DB::table('musics_directories')->where('music_id', $edit_id)->first()->directory_id;

            foreach ($request->localeData as $localeData) {
                $name = $localeData['name'];
                $displayName = $localeData['display_name'];
            }

            DB::table('musics')->where('id', $edit_id)->update(['local_filename'=>$name]);
            DB::table('musics')->where('id', $edit_id)->update(['display_name'=>$displayName]);
            DB::table('musics')->where('id', $edit_id)->update(['server_name'=>$displayName]);
            DB::table('musics')->where('id', $edit_id)->update(['updated_at'=>\Carbon\Carbon::now()]);

            DB::table('directories')->where('id',$edit_directories_id)->update([
                "updated_at" =>  \Carbon\Carbon::now(),
            ]);

            $return_value = $name . " " . $displayName;

            return redirect()->back()->with('message', __('cms_folder.edit_success', ['folder' => $return_value]));

        }
        else{
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }

    public function addDirectory(Request $request,$id) {

        $validator = $this->validateAddDirectoryData($request);

        if(!$validator->fails()){
            $parent_id = $id;

            foreach ($request->localeData as $localeData) {
                $name = $localeData['name'];
                $displayName = $localeData['display_name'];
            }

            $directory_id = DB::table('directories')->insertGetId([
                'name' => $name,
                'display_name' => $displayName,
                'parent_id' => $parent_id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                ]);

            $local_id = DB::table('directories_local')
                ->where('directories_id',$parent_id)
                ->first()->local_id;

            DB::table('directories_local')->insert([
                'directories_id' => $directory_id,
                'local_id' => $local_id

                ]);
            $return_value = $name . " " . $displayName;

            DB::table('directories')->where('id',$id)->update([
                "updated_at" =>  \Carbon\Carbon::now(),
            ]);

            return redirect()->back()->with('message', __('cms_folder.add_success', ['folder' => $return_value]));
        }
        else{
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
    }

    public function addFile(Request $request,$id) {
        Log::notice("                   addFile");
        Log::info($request);

        $validator = $this->validateAddFileData($request);

        if(!$validator->fails()){
            $directory_id = $id;

            foreach ($request->localeData as $localeData) {
                $name = $localeData['name'];
                $displayName = $localeData['display_name'];
            }

            $request->file('mp3')->storeAs('ServerStorage/mp3', $displayName);
            
            $music_id = DB::table('musics')->insertGetId([
                'local_filename' => $name,
                'display_name' => $displayName,
                'server_name' => $displayName,
                'is_auto_download' => 'no',
                "created_at" =>  \Carbon\Carbon::now(),
                "updated_at" =>  \Carbon\Carbon::now(),
                ]);

            $local_id = DB::table('directories_local')->where('directories_id',$directory_id)->first()->local_id;

            DB::table('music_local')->insert([
                'music_id' => $music_id,
                'local_id' => $local_id,
                ]);

            DB::table('musics_directories')->insert([
                'music_id' => $music_id,
                'directory_id' => $directory_id,
                ]);

            DB::table('directories')->where('id',$id)->update([
                "updated_at" =>  \Carbon\Carbon::now(),
            ]);

            return redirect()->back()->with('message', __('cms_folder.add_success', ['folder' => $displayName]));
        }
        else{
            return redirect()->back()->withErrors($validator)->withInput();
        }        
    }

    private function validateEditDirectoryData($request) {
        return Validator::make($request->all(), [
            'localeData.*.name' => 'required',
            'localeData.*.display_name' => 'required',
        ]);
    }

    private function validateEditFileData($request) {
        return Validator::make($request->all(), [
            'localeData.*.name' => 'required| regex:/.*\.mp3$/',
            'localeData.*.display_name' => 'required| regex:/.*\.mp3$/',
        ]);
    }

    private function validateAddDirectoryData($request) {
        return Validator::make($request->all(), [
            'localeData.*.name' => 'required',
            'localeData.*.display_name' => 'required',
        ]);
    }

    private function validateAddFileData($request) {
        return Validator::make($request->all(), [
            'localeData.*.name' => 'required | regex:/.*\.mp3$/',
            'localeData.*.display_name' => 'required| regex:/.*\.mp3$/',
            'mp3' => 'required|mimes:mpga'
        ]);
    }
}
