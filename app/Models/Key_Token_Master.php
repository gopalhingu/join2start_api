<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Key_Token_Master extends Model
{
    public $table="key_token_master";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'token',
    ];

}
