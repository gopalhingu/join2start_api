<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Appointment extends Authenticatable
{
    use SoftDeletes;

    use HasFactory;
    public $table = 'appointment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'dr_id', 
        'date', 
        'time', 
        'payment_status', 
        'total_payed', 
        'payment_type'
    ];

    public function user()
    {
    	return $this->belongsTo('App\Models\User','dr_id','id')->withDefault();
    }

}
