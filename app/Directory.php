<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    //
    protected $table = 'directories';

    public function local(){
    	return $this->belongsTo('App\Local');
    }

    public function music(){
    	return $this->hasMany('App\Music');
    }
}
