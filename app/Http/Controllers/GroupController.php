<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Group;
use App\Msgbox;
use GatewayClient\Gateway;
class GroupController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if(!$query){
            return [];
        }
        $count = Group::where('groupname','like','%'.$query.'%')->get()->count();
        $groups = Group::where('groupname','like','%'.$query.'%')->with('user')->paginate(10);

        return json_encode(['code','msg','data'=>['limit'=>10,'count'=>$count,'lists'=>$groups]]);
    }

    public function applyGroup(Request $request)
    {
        $this->validate($request,[
            'group.id'=>'required',
            'group.user_id'=>'required',
        ]);


        $user = \Auth::user();
        //是否添加群
        $group = Group::where('id',$request->input('group.id'))->whereHas('user')->firstOrFail();
        $userIds = $group->users()->get()->pluck('id')->toarray();
        if(in_array($user->id,$userIds)){
            return json_encode(['code'=>1,'msg'=>'已加入群','data'=>[]]);
        }


        if($msg=Msgbox::where('uid',$request->input('group.user_id'))->where('from',\Auth::user()->id)->where('from_group',$request->input('group.id'))->where('state','')->where('type',Msgbox::TYPE_GROUP)->first()){
            $msg->delete();
        }
        $msg =Msgbox::create([
            'uid'=>$request->input('group.user_id'),
            'from'=>\Auth::user()->id,
            'from_group'=>$request->input('group.id'),
            'remark'=>$request->input('remark'),
            'content'=>'申请加入群:'.$group->groupname,
            'type'=>'group',
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
            $data['type'] = 'msgBox';
            $data['content'] = Msgbox::where('uid',$msg->uid)->where('read',0)->get()->count();
            Gateway::sendToUid($msg->uid, json_encode($data));
            $msg->read =1;
            $msg->save();
        }
        return json_encode(['code'=>0,'msg'=>'','data'=>[]]);
    }

    public function agreeGroup(Request $request)
    {
        $this->validate($request,[
            'id'=>'required',
        ]);

        $msg = Msgbox::findOrfail($request->input('id'));
        if($msg->state!=''&&$msg->from_group){
            return json_encode(['code'=>1,'msg'=>'状态错误','data'=>[]]);
        }
        $user = \Auth::user();
        if($user->id!=$msg->uid){
            return json_encode(['code'=>1,'msg'=>'状态错误','data'=>[]]);
        }

        //加组
        $group = Group::findOrFail($msg->from_group);
        $group->users()->syncWithoutDetaching($msg->from);


        Gateway::$registerAddress = '127.0.0.1:1238';



        //改变同意状态
        $msg->read = 0;//健壮
        $msg->state = Msgbox::STATE_TYPE_AGREE;
        $msg->save();

        //给对方发送信息
        $sendMsg = Msgbox::create([
            'content'=>$user->nickname.'   已经同意你的入群申请',
            'uid'=>$msg->from,
            'from'=>$msg->uid,
            'type'=>'system',
            'receive_time'=>date('Y-m-d H:i:s'),
            'user'=>[
                'id'=>null,
            ]
        ]);

        if(Gateway::isUidOnline($sendMsg->uid)){

            //加入群
            $endUser = User::find($sendMsg->uid);
            Gateway::joinGroup($endUser->client_id, $group->id);
            //推送加群消息
            $m['type'] = 'group';
            $m['avatar'] =$group->avatar;
            $m['id'] = $group->id;
            $m['groupname'] = $group->groupname;
            $data['type'] = 'addList';
            $data['content'] = $m;
            Gateway::sendToUid($sendMsg->uid, json_encode($data));

            //todo 推送消息盒子
            $data1['type'] = 'msgBox';
            $data1['content'] = Msgbox::where('uid',$sendMsg->uid)->where('read',0)->get()->count();
            Gateway::sendToUid($sendMsg->uid, json_encode($data1));
            $sendMsg->read =1;
            $sendMsg->save();

        }
        return json_encode(['code'=>0,'msg'=>'','data'=>[]]);

    }

    public function refuseGroup(Request $request)
    {
        $this->validate($request,[
            'id'=>'required',
        ]);
        $msg = Msgbox::findOrfail($request->input('id'));
        if($msg->state!=''){
            return json_encode(['code'=>1,'msg'=>'状态错误','data'=>[]]);
        }
        $user = \Auth::user();
        if($user->id!=$msg->uid){
            return json_encode(['code'=>1,'msg'=>'状态错误','data'=>[]]);
        }

        $msg->read = 0;//健壮
        $msg->state = Msgbox::STATE_TYPE_REFUSE;
        $msg->save();

        //给对方发送信息
        $sendMsg = Msgbox::create([
            'content'=>$user->nickname.'   拒绝了你的入群申请',
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

    public function getMembers(Request $request)
    {
        $user=\Auth::user();
        $group = $user->groups()->where('groups.id',$request->input('id'))->first();
        if(!$group){
            return json_encode(['code'=>1,'msg'=>'','data'=>['list'=>[]]]);
        }
        $users = $group->users()->get();
        return json_encode(['code'=>0,'msg'=>'','data'=>['list'=>$users]]);

    }

    public function store(Request $request)
    {
        $groupname = $request->input('groupname');
        $avatar = $request->input('avatar');

        if(!$groupname||!$avatar){
            return json_encode(['code'=>1,'msg'=>'缺少参数']);
        }
        $user = \Auth::user();
        $group = new Group();
        $group->groupname = $groupname;
        $group->avatar = $avatar;
        $group->user_id = $user->id;
        $group->save();
        //自己加进去
        $user->groups()->syncWithoutDetaching($group->id);
        Gateway::$registerAddress = '127.0.0.1:1238';
        //加入websocket
        Gateway::joinGroup($user->client_id, $group->id);

        return json_encode(['code'=>0,'msg'=>'','data'=>$group->getGroupData()]);
    }
}
