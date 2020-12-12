<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Advisor extends Model
{
    public $fillable = [
        'admin_id',
        'advisor_id',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'advisor_id ', 'id');
    }
    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }
    public function clients()
    {
        return $this->belongsTo(User::class,'advisor_id');
    }
}
