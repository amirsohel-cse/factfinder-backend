<?php

namespace App;

use App\Models\Admin;
use App\Models\Advisor;
use App\Models\Client;
use App\Models\Task;
use App\Models\Vision;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasRoles;
    use HasApiTokens, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'state',
        'phone',
        'image',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRoleAttribute()
    {
        $role = $this->roles->first();
        return $role ? $role->name : '';
    }

    public function admins()
    {
        return $this->hasMany(Admin::class,'super_admin_id');
    }
    public function advisors()
    {
        return $this->hasMany(Advisor::class,'admin_id');
    }
    public function clients()
    {
        return $this->hasMany(Client::class,'advisor_id');
    }
    public function visions()
    {
        return $this->hasMany(Vision::class);
    }
    public function tasks()
    {
        return $this->belongsToMany(Task::class)
                    ->withPivot('status')
                    ->withTimestamps();
    }
    public function creatorTasks(){

        return $this->hasMany(Task::class,'user_id');
    }
    public function clientTask()
    {
       return $this->belongsToMany(Task::class,'task_user')
                   ->withPivot('status')
                   ->withTimestamps();
    }
    public function scopeRoleUser($query,$roleName){

        return $query->whereHas('roles',function ($q) use ($roleName){
                    $q->where('name',$roleName);
        });
    }
}
