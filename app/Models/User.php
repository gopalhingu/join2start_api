<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;

    use HasFactory;
    public $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'country_code', 'gender', 'password', 'confirm_password', 'profile_image', 'language', 'user_type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

        /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required',
        'phone' => 'required',
        'gender' => 'required',
        'country_code' => 'required',
        'password' => 'required',
        'confirm_password' => 'required',
        'user_type' => 'required'
    ];

    public function appointments()
    {
    	return $this->belongsTo('App\Models\Appointment','dr_id','id')->withDefault();
    }
}
