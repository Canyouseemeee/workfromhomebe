<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTask extends Model
{
    protected $table ='statustask';
    protected $primaryKey = 'statustaskid';
    protected $fillable = [
        'statustaskid','statustaskname'
    ];
}
