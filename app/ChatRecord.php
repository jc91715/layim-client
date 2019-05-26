<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatRecord extends Model
{
    protected $fillable = ['send_id','receive_id','group_id','type','content','if_read'];

    protected $casts = [
        'content'=>'array'
    ];

    protected $appends = ['contentTemp'];

    public function getContentTempAttribute()
    {
        $content = $this->content;
        if($this->send_id==\Auth::user()->id){
            $content['content']['mine'] = true;
        }
        return $content['content'];
    }

}
