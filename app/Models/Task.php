<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'completion_date',
        'completion_time',
        'priority',
        'assignee_type',
        'user_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('status')
                    ->withTimestamps();
    }
    public function pending()
    {
        return $this->belongsToMany(User::class)->wherePivot('status',0);
    }
    public function scopeActiveTask($query)
    {
        return $query->where('status',0)->where('completion_date','>=',Carbon::now());
    }
    public function scopeOverDueTask($query)
    {
        return $query->where('status',0)->where('completion_date','<=',Carbon::now());
    }
    public function scopeAdminActiveTask($query)
    {
        return $query->where('completion_date','>=',Carbon::now());
    }
}
