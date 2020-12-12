<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    public $fillable = [
        'admin_id',
        'super_admin_id',
        'max_advisor'
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'super_admin_id ', 'id');
    }
}
