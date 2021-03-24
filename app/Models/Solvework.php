<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solvework extends Model
{
    protected $table ='solvework';
    protected $primaryKey = 'solveworkid';
    protected $fillable = [
        'solveworkid','taskid','createsolvework','subject','statussolvework','departmentid','assignment','file','assign_date','due_date','close_date','created_at','updated_at'
    ];
}
