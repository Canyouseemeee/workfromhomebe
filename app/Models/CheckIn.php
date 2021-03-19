<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    protected $table ='checkin_work';
    protected $primaryKey = 'checkinid';
    protected $fillable = [
        'userid','date_start','date_end','status','date_in','file','latitude','longitude','created_at','updated_at'
    ];
}
