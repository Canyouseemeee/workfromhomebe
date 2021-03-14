<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statuscheckin extends Model
{
    protected $table ='statuscheckin';
    protected $primaryKey = 'statusid';
    protected $fillable = [
        'statusid','statusname','created_at','updated_at'
    ];
}
