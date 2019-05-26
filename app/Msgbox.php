<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Msgbox extends Model
{
    const STATE_TYPE_AGREE ='agree';
    const STATE_TYPE_REFUSE ='refuse';
    const TYPE_FRIEND='friend';
    const TYPE_GROUP='group';
    public static $stateTypeMaps = [
        ''=>'',
        self::STATE_TYPE_AGREE=>'已同意',
        self::STATE_TYPE_REFUSE=>'已拒绝'
    ];

    protected $fillable = ['uid','from','from_group','remark','href','receive_time','user','state','type','content'];

    protected $casts = [
        'user'=>'array'
    ];
    protected $appends = ['time','stateTemp'];

    public function getTimeAttribute()
    {
        return $this->get_last_time(strtotime(date($this->receive_time)));
    }

    public function getStateTempAttribute()
    {
        return self::$stateTypeMaps[$this->state];
    }
    function get_last_time($targetTime)
    {
        // 今天最大时间
        $todayLast   = strtotime(date('Y-m-d 23:59:59'));
        $agoTimeTrue = time() - $targetTime;
        $agoTime     = $todayLast - $targetTime;
        $agoDay      = floor($agoTime / 86400);

        if ($agoTimeTrue < 60) {
            $result = '刚刚';
        } elseif ($agoTimeTrue < 3600) {
            $result = (ceil($agoTimeTrue / 60)) . '分钟前';
        } elseif ($agoTimeTrue < 3600 * 12) {
            $result = (ceil($agoTimeTrue / 3600)) . '小时前';
        } elseif ($agoDay == 0) {
            $result = '今天 ' . date('H:i', $targetTime);
        } elseif ($agoDay == 1) {
            $result = '昨天 ' . date('H:i', $targetTime);
        } elseif ($agoDay == 2) {
            $result = '前天 ' . date('H:i', $targetTime);
        } elseif ($agoDay > 2 && $agoDay < 16) {
            $result = $agoDay . '天前 ' . date('H:i', $targetTime);
        } else {
            $format = date('Y') != date('Y', $targetTime) ? "Y-m-d H:i" : "m-d H:i";
            $result = date($format, $targetTime);
        }
        return $result;
    }
}
