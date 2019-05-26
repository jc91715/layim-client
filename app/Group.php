<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class,'user_group');
    }

    public function user()
    {
        return $this->belongsto(User::class);
    }

    public function getGroupData()
    {
        return [
            'id'=>$this->id,
            'groupname'=>$this->groupname,
            'avatar'=>$this->avatar,
            'type'=>'group',
            'members'=>1,
        ];
    }
}
