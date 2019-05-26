<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupType extends Model
{

    public function lists()
    {
        return $this->belongsToMany(User::class,'user_group_type');
    }
}
