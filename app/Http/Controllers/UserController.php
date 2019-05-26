<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\GroupType;
use App\Msgbox;
use App\ChatRecord;
use GatewayClient\Gateway;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if(!$query){
            return [];
        }
        $count = User::where('nickname','like','%'.$query.'%')->orWhere('name','like','%'.$query.'%')->get()->count();
        $users = User::where('nickname','like','%'.$query.'%')->orWhere('name','like','%'.$query.'%')->paginate(10);

        return json_encode(['code','msg','data'=>['limit'=>10,'count'=>$count,'lists'=>$users]]);
    }
    //申请加好友
    public function applyFriend(Request $request)
    {
        $this->validate($request,[
            'uid'=>'required',
            'from_group'=>'required'
        ]);

        if($request->input('uid')==\Auth::user()->id)
            return json_encode(['code'=>1,'msg'=>'状态错误','data'=>[]]);
        $user = \Auth::user();
        //是否添加过好友
        $lists = $user->groupTypes()->with('lists')->get()->pluck('lists');
        foreach ($lists as $list){
            if(in_array($request->input('uid'),$list->pluck('id')->toarray())){
                return json_encode(['code'=>1,'msg'=>'已添加过好友','data'=>[]]);
            }
        }

        if($msg=Msgbox::where('uid',$request->input('uid'))->where('from',\Auth::user()->id)->where('state','')->where('type',Msgbox::TYPE_FRIEND)->first()){
            $msg->delete();
        }
        $msg =Msgbox::create([
            'uid'=>$request->input('uid'),
            'from'=>\Auth::user()->id,
            'from_group'=>$request->input('from_group'),
            'remark'=>$request->input('remark'),
            'content'=>'申请添加你为好友',
            'type'=>'friend',
            'receive_time'=>date('Y-m-d H:i:s'),
            'user'=>[
                "id"=> \Auth::user()->id,
                "avatar"=> \Auth::user()->headimgurl,
                "username"=> \Auth::user()->nickname,
                "sign"=> \Auth::user()->sign
            ]
        ]);
        Gateway::$registerAddress = '127.0.0.1:1238';
        if(Gateway::isUidOnline($msg->uid)){

            //todo 推送消息盒子
            $data['type'] = 'msgBox';
            $data['content'] = Msgbox::where('uid',$msg->uid)->where('read',0)->get()->count();
            Gateway::sendToUid($msg->uid, json_encode($data));
            $msg->read =1;
            $msg->save();


        }
        return json_encode(['code'=>0,'msg'=>'','data'=>[]]);
    }

    public function agreeFriend(Request $request)
    {
        $this->validate($request,[
            'id'=>'required',
            'group'=>'required'
        ]);

        $msg = Msgbox::findOrfail($request->input('id'));
        if($msg->state!=''){
            return json_encode(['code'=>1,'msg'=>'状态错误','data'=>[]]);
        }
        $user = \Auth::user();
        if($user->id!=$msg->uid||$user->id==$msg->from){
            return json_encode(['code'=>1,'msg'=>'状态错误','data'=>[]]);
        }
        $selfFGroupType = GroupType::where('id',$request->input('group'))->where('user_id',$user->id)->firstOrfail();
        $otherFGroupType = GroupType::where('id',$msg->from_group)->where('user_id',$msg->from)->firstOrfail();

        //互相加入分组
        $selfFGroupType->lists()->syncWithoutDetaching($msg->from);
        $otherFGroupType->lists()->syncWithoutDetaching($msg->uid);
        //改变同意状态
        $msg->read = 0;//健壮
        $msg->state = Msgbox::STATE_TYPE_AGREE;
        $msg->save();

        //给对方发送信息
        $sendMsg = Msgbox::create([
            'content'=>$user->nickname.'   已经同意你的好友申请',
            'uid'=>$msg->from,
            'from'=>$msg->uid,
            'type'=>'system',
            'receive_time'=>date('Y-m-d H:i:s'),
            'user'=>[
                'id'=>null,
            ]
        ]);
        Gateway::$registerAddress = '127.0.0.1:1238';
        if(Gateway::isUidOnline($sendMsg->uid)){
            //推送同意加好友消息
            $m['type'] = 'friend';
            $m['avatar'] = \Auth::user()->headimgurl;
            $m['groupid'] = $msg->from_group;
            $m['id'] = \Auth::user()->id;
            $m['username'] = \Auth::user()->nickname;
            $m['sign'] = \Auth::user()->sign;
            $data['type'] = 'addList';
            $data['content'] = $m;
            Gateway::sendToUid($msg->from, json_encode($data));

            //todo 推送系统消息 buxianshi
            $data1['type'] = 'msgBox';
            $data1['content'] = Msgbox::where('uid',$sendMsg->uid)->where('read',0)->get()->count();
            Gateway::sendToUid($sendMsg->uid, json_encode($data1));
            $sendMsg->read =1;
            $sendMsg->save();

        }
        return json_encode(['code'=>0,'msg'=>'','data'=>[]]);

    }
    public function refuseFriend(Request $request)
    {
        $this->validate($request,[
            'id'=>'required',
        ]);
        $msg = Msgbox::findOrfail($request->input('id'));
        if($msg->state!=''){
            return json_encode(['code'=>1,'msg'=>'状态错误','data'=>[]]);
        }
        $otherFGroupType = GroupType::where('id',$msg->from_group)->where('user_id',$msg->from)->firstOrfail();
        $user = \Auth::user();
        $msg->read = 0;//健壮
        $msg->state = Msgbox::STATE_TYPE_REFUSE;
        $msg->save();

        //给对方发送信息
        $sendMsg = Msgbox::create([
            'content'=>$user->nickname.'   拒绝了你的好友申请',
            'uid'=>$msg->from,
            'from'=>$msg->uid,
            'type'=>'system',
            'receive_time'=>date('Y-m-d H:i:s'),
            'user'=>[
                'id'=>null,
            ]
        ]);
        Gateway::$registerAddress = '127.0.0.1:1238';
        if(Gateway::isUidOnline($sendMsg->uid)){
            //todo 推送消息盒子
            $data1['type'] = 'msgBox';
            $data1['content'] = Msgbox::where('uid',$sendMsg->uid)->where('read',0)->get()->count();
            Gateway::sendToUid($sendMsg->uid, json_encode($data1));
            $sendMsg->read =1;
            $sendMsg->save();
        }

        return json_encode(['code'=>0,'msg'=>'','data'=>[]]);
    }

    public function setSign(Request $request)
    {
        $sign = $request->input('sign');
        if(!$sign){
            return ;
        }
        $user=\Auth::user();
        $user->sign=$sign;
        $user->save();
    }

    public function setStatus(Request $request)
    {
        $status = $request->status;

        if(!in_array($status,['online','hide'])){
            return ;
        }
        if($status=='hide'){
            $status='offline';
        }
        $user=\Auth::user();
        $user->status=$status;
        $user->save();
        Gateway::$registerAddress = '127.0.0.1:1238';
        $groupTypes = $user->groupTypes()->with('lists')->get();
        $groupTypes->each(function($groupType)use($status,$user){
            $groupType->lists->each(function($user1)use($status,$user){
                $data['type'] = 'userStatus';
                $data['content'] = ['id'=>$user->id,'status'=>$status];
                Gateway::sendToUid($user1->id, json_encode($data));
            });
        });


    }
    public function chatRecord(Request $request)
    {
        $id = $request->input('id');
        $type= $request->input('type');

        if(!$id||!in_array($type,['friend','group'])){
            return json_encode(['code'=>1,'msg'=>'参数错误']);
        }
        $count =0;
        $records = collect([]) ;
        switch ($type){
            case 'friend':
                $count =ChatRecord::where(function($query)use($id){
                    $query->where(function($query)use($id){
                        $query->where('send_id',$id)->where('receive_id',\Auth::user()->id);
                    })->orWhere(function($query)use($id){
                        $query->where('send_id',\Auth::user()->id)->where('receive_id',$id);
                    });
                })->where('type','friend')->get()->count();
               $records =  ChatRecord::where(function($query)use($id){
                   $query->where(function($query)use($id){
                       $query->where('send_id',$id)->where('receive_id',\Auth::user()->id);
                    })->orWhere(function($query)use($id){
                       $query->where('send_id',\Auth::user()->id)->where('receive_id',$id);
                   });
               })->where('type','friend')->oldest('created_at')->paginate(8);
                break;
            case 'group':
                $count = ChatRecord::where(function($query){
                    $query->where('receive_id',\Auth::user()->id);
                })->where('group_id',$id)->where('type','group')->get()->count();
                $records = ChatRecord::where(function($query){
                    $query->where('receive_id',\Auth::user()->id);
                })->where('group_id',$id)->where('type','group')->oldest('created_at')->paginate(8);
                break;
        }

        $records = $records->pluck('contentTemp')->sortBy('timestamp')->toarray();

        return json_encode(['code','msg','data'=>['limit'=>8,'count'=>$count,'records'=>$records]]);
    }
}
