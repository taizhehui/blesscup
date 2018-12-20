<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    //
    protected $table = 'chats';

    public function device()
    {
    	return $this->belongsTo('App\Device','owner_id','uuid');
    }

    public function message()
    {
    	return $this->hasMany('App\Message');
    }
}
