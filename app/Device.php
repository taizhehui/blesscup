<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //
    protected $table = 'devices';

    public function user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }

    public function music()
    {
    	return $this->hasMany('App\Music');
    }

    public function directory()
    {
        return $this->hasMany('App\Directory');
    }

    public function version()
    {
        return $this->hasOne('App\Version');
    }

    public function local()
    {
        return $this->hasOne('App\Local');
    }
    public function chat()
    {
        return $this->hasMany('App\Chat', 'owner_id' , 'uuid');
    }
}
