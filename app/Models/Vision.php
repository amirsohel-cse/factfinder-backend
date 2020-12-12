<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Vision extends Model
{
    protected $table="visions";

    protected $fillable=[
        'thumbnail_image',
        'original_image',
        'user_id'
    ];

    public function users()
    {
        $this->belongsTo(User::class);
    }
}
