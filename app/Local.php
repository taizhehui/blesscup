<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    //
    protected $table = 'locals';

    public function music(){
    	return $this->hasMany('App\Music');
    }

    public function directory(){
    	return $this->hasMany('App\Directory');
    }
}
