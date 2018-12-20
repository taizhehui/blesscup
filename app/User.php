<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{

    use Notifiable;
	
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'name', 'email', 'password', 'gender', 'dob', 'user_code',
	];
	
	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
	protected $hidden = [
		'password', 'remember_token',
    ];
    
    // protected $dates = ['deleted_at'];

    /**
     * @return mixed
     */
    public function device()
    {
        return $this->hasMany('App\Device','user_id','id');
    }

    public function message()
    {
        return $this->hasMany('App\Message');
    }
}
