<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\GroupType;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function mobile()
    {

        $this->init();

        $user = \Auth::user();
        $userlist['mine'] = $user->getMine();
        $userlist['friend'] = $user->getFriends();
        $userlist['group'] = $user->getGroups();
        $wbsocketIp = config('app.wbsocket_ip');
        return view('layim.mobile',['userlist'=>$userlist,'wbsocket_ip'=>$wbsocketIp]);
    }
    public function pc()
    {

        $this->init();
        $user = \Auth::user();
        $userlist['mine'] = $user->getMine();
        $userlist['friend'] = $user->getFriends();
        $userlist['group'] = $user->getGroups();
        $wbsocketIp = config('app.wbsocket_ip');
        return view('layim.pc',['userlist'=>$userlist,'wbsocket_ip'=>$wbsocketIp]);
    }
    //手机版可能要用到
    public function msgbox()
    {
        return view('layim.msgbox');
    }

    protected function init()
    {
        $user = \Auth::user();
        //自动创建当天群
        $group = Group::whereBetween('created_at',[date('Y-m-d'),date('Y-m-d 23:59:59')])->first();
        if(!$group){
            $group = new Group();
            $group->groupname = date('Y-m-d').'当天群';
            $group->avatar = 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif';
            $group->user_id = $user->id;
            $group->save();
            //自己加进去
            $user->groups()->syncWithoutDetaching($group->id);
        }


        $groupType = GroupType::where('user_id',$user->id)->first();
        //自动创建默认分组
        if(!$groupType){
            $group = new GroupType();
            $group->groupname = '我的好友';
            $group->user_id =$user->id;
            $group->save();
        }



    }

    public function uploadImage(Request $request)
    {

        $path = $request->file('file')->store('chat/images','public');

        return json_encode(['code'=>0,'msg'=>'','data'=>['src'=> asset('storage/'.$path)]]);

    }
    public function uploadFile(Request $request)
    {

        $path = $request->file('file')->store('chat/files','public');

        return json_encode(['code'=>0,'msg'=>'','data'=>['src'=> asset('storage/'.$path),'name'=>$request->file('file')->getClientOriginalName()]]);

    }
}
