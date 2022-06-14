<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User_details extends Authenticatable
{
    use SoftDeletes;

    use HasFactory;
    public $table = 'user_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'language', 
        'country', 
        'age', 
        'therapy_type', 
        'gender', 
        'relationship_status', 
        'identify_self', 
        'financial_status', 
        'sleeping_habits', 
        'is_religious',
        'want_special_session',
        'therapy_taken',
        'expectations' ,
        'is_medication',
        'thought_about_suicide',
        'is_feel_anxieties',
        'therapy_consideration',
        'therapist_preference',
        'other_detail',
        'source'
    ];

}
