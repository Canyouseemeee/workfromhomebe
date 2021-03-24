<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table ='task';
    protected $primaryKey = 'taskid';
    protected $fillable = [
        'taskid','createtask','subject','description','statustask','departmentid','assignment','file','assign_date','due_date','close_date','created_at','updated_at'
    ];
}
