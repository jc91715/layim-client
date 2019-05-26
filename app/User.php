<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
        'openid',
        'nickname',
        'sex',
        'language',
        'city',
        'province',
        'country',
        'headimgurl'
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

    protected $appends = ['username','avatar'];

    protected static function boot()
    {
       parent::boot();
        self::creating(function($model){
            $model->headimgurl = 'http://laravel-layim.jc91715.top/storage/chat/images/0A5XVFVc26gAibXFwNQ5oHfyEj8qKSO0veJrlybT.jpeg';
        });
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class,'user_group');
    }
    public function groupTypes()
    {
        return $this->hasMany(GroupType::class);
    }
    public function getMine()
    {
        return [
            "username"=>$this->nickname,
            "id"=> $this->id,
            "status"=> "online",
            "sign"=> $this->sign,
            "avatar"=> $this->headimgurl
        ];
    }

    public function getFriends()
    {
        return $this->groupTypes()->with('lists')->get()->map(function($item){
            $item->list=$item->lists;
            return $item;
        })->toarray();
    }

    public function getGroups()
    {
        return $this->groups()->get()->toarray();
    }

    public function getNicknameAttribute()
    {
        return $this->nickname??$this->name;
    }

    public function getUsernameAttribute()
    {
        return $this->nickname;
    }
    public function getAvatarAttribute()
    {
        return $this->headimgurl;
    }
}
