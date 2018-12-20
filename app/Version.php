<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $table = 'versions';

    public function device(){
    	return $this->belongsTo('App\Device');
    }
}
