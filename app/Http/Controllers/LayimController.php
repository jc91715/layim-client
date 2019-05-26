<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GatewayClient\Gateway;
use App\Group;
use App\ChatRecord;
use App\Msgbox;


class LayimController extends Controller
{
    public function bind(Request $request)
    {
        if(!$request->input('client_id')){
            return json_encode(['code'=>1,'msg'=>'缺少参数','data'=>[]]);
        }
        Gateway::$registerAddress = '127.0.0.1:1238';


        $user = \Auth::user();
        $user->client_id = $request->input('client_id');
        $user->save();

        Gateway::bindUid($request->input('client_id'), $user->id);

        //入群
        $user->groups()->get()->each(function($item)use($request){
            Gateway::joinGroup($request->input('client_id'), $item->id);
        });

        //发送未读消息
        $records = ChatRecord::where('receive_id',$user->id)->where('if_read',0)->get();
        $records->each(function ($item)use($user){
            $chatMessage =   $item->content;
            if($item->send_id==$user->id){
                $chatMessage['content']['mine'] = true;
            }
            $item->if_read = 1;
            $item->save();
            Gateway::sendToUid($item->receive_id, json_encode($chatMessage));
        });

        //好友相关 发送消息盒子
        $msgs = Msgbox::where('uid',$user->id)->where('read',0)->get();
        $count = count($msgs);
        if($count){

            $data['type'] = 'msgBox';
            $data['content'] = $count;
            Gateway::sendToUid($user->id, json_encode($data));
            $msgs->each(function($it){
                $it->read = 1;
                $it->save();
            });
        }

        return json_encode(['code'=>0,'msg'=>'','data'=>[]]);
    }

    public function sendMessage(Request $request)
    {
        $user = \Auth::user();
        Gateway::$registerAddress = '127.0.0.1:1238';

        $content=$request->input('content');
        $msg['username'] =$content['mine']['username'];
        $msg['avatar'] = $content['mine']['avatar'];
        $msg['id'] = $content['mine']['id'];
        $msg['content'] = $content['mine']['content'];
        $msg['type'] = $content['to']['type'];
        $msg['timestamp'] = time()*1000;
        $chatMessage['type'] = 'getMessage';
        $chatMessage['content'] = $msg;


//        $data['content']['uid'] = $user->id;

        switch ($content['to']['type']){
            case 'group':
                $chatMessage['content']['id'] = $content['to']['id'];

                //群组消息入库
                $group = Group::find($content['to']['id']);
                $users = $group->users()->get();

                $users->each(function($item)use($user,$chatMessage){

                    if(Gateway::isUidOnline($item->id)){
                        ChatRecord::create([
                            'send_id'=>$user->id,
                            'receive_id'=>$item->id,
                            'group_id'=>$chatMessage['content']['id'],
                            'content'=>$chatMessage,
                            'type'=>'group',
                            'if_read'=>1
                        ]);
                    }else{//不在线的群用户记录未读消息
                        ChatRecord::create([
                            'send_id'=>$user->id,
                            'receive_id'=>$item->id,
                            'group_id'=>$chatMessage['content']['id'],
                            'content'=>$chatMessage,
                            'type'=>'group',
                        ]);
                    }
                });

                Gateway::sendToGroup($chatMessage['content']['id'], json_encode($chatMessage),Gateway::getClientIdByUid($user->id));
                break;
            case 'friend':

                //单聊入库
                if(Gateway::isUidOnline($content['to']['id'])){//用户在线
                    \Log::info('用户'.$content['to']['id'].'在线');
                    ChatRecord::create([
                        'send_id'=>$user->id,
                        'receive_id'=>$content['to']['id'],
                        'content'=>$chatMessage,
                        'type'=>'friend',
                        'if_read'=>1
                    ]);
                    Gateway::sendToUid($content['to']['id'], json_encode($chatMessage));
                }else{
                    \Log::info('用户'.$content['to']['id'].'不在线');

                    ChatRecord::create([
                        'send_id'=>$user->id,
                        'receive_id'=>$content['to']['id'],
                        'content'=>$chatMessage,
                        'type'=>'friend',
                    ]);
                }

                break;
        }


    }


    public function groupMembers($group)
    {
        //todo 是否可拉取群成员信息

        $group = Group::where('id',$group)->firstOrfail();

        $members = $group->users()->get();

        return json_encode(['code'=>0,'msg'=>'','data'=>[
            'members'=>$members
        ]]);
    }

    public function msgbox()
    {
        Msgbox::where('uid',\Auth::user()->id)->where('read',0)->update(['read'=>1]);
        $ms = Msgbox::where('uid',\Auth::user()->id)->latest('receive_time')->paginate(10)->toarray();

        $pages= ceil($ms['total']/10);
        return json_encode(['code'=>0,'pages'=>$pages,'data'=>$ms['data']]);
        return json_encode(json_decode('{
    "code": 0,
    "pages": 1,
    "data": [
        {
            "id": 76,
            "content": "申请添加你为好友",
            "uid": 168,
            "from": 166488,
            "from_group": 0,
            "type": 1,
            "remark": "有问题要问",
            "href": null,
            "read": 1,
            "time": "刚刚",
            "user": {
                "id": 166488,
                "avatar": "http://q.qlogo.cn/qqapp/101235792/B704597964F9BD0DB648292D1B09F7E8/100",
                "username": "李彦宏",
                "sign": null
            }
        },
        {
            "id": 75,
            "content": "申请添加你为好友",
            "uid": 168,
            "from": 347592,
            "from_group": 0,
            "type": 1,
            "remark": "你好啊！",
            "href": null,
            "read": 1,
            "time": "刚刚",
            "user": {
                "id": 347592,
                "avatar": "http://q.qlogo.cn/qqapp/101235792/B78751375E0531675B1272AD994BA875/100",
                "username": "麻花疼",
                "sign": null
            }
        },
        {
            "id": 62,
            "content": "雷军 拒绝了你的好友申请",
            "uid": 168,
            "from": null,
            "from_group": null,
            "type": 1,
            "remark": null,
            "href": null,
            "read": 1,
            "time": "10天前",
            "user": {
                "id": null
            }
        },
        {
            "id": 60,
            "content": "马小云 已经同意你的好友申请",
            "uid": 168,
            "from": null,
            "from_group": null,
            "type": 1,
            "remark": null,
            "href": null,
            "read": 1,
            "time": "10天前",
            "user": {
                "id": null
            }
        },
        {
            "id": 61,
            "content": "贤心 已经同意你的好友申请",
            "uid": 168,
            "from": null,
            "from_group": null,
            "type": 1,
            "remark": null,
            "href": null,
            "read": 1,
            "time": "10天前",
            "user": {
                "id": null
            }
        }
    ]
}',true));
    }
}
