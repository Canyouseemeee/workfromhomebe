<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusSolvework extends Model
{
    protected $table ='statussolvework';
    protected $primaryKey = 'statussolveworkid';
    protected $fillable = [
        'statussolveworkid','statussolveworkname'
    ];
}
