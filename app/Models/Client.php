<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public $fillable = [
        'advisor_id',
        'client_id'
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }
    public function client()
    {
        return $this->belongsTo(User::class,'client_id');
    }
}
