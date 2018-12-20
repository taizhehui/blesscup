<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    //
    protected $table = 'musics';

    protected $fillable = [
    	'local_filename'
    ];
    protected $hidden = [
    	'server_filename' , 'is_auto_download',
    ];

    public function local(){
    	 return $this->belongsToMany('App\Local');
    }

    public function directory(){
    	return $this->belongsToOne('App\Directory');
    }

}
