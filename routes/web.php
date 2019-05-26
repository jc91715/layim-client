<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    return redirect(route('login'));
});


Auth::routes();

Route::get('wechat/redirect','WechatController@redirect')->name('wechat.redirect');
Route::get('wechat/callback','WechatController@callback')->name('wechat.callback');

Route::get('/home', 'HomeController@index')->name('home');


Route::group(['middleware'=>'auth'],function(){
    //\Auth::login(\App\User::find(2));
    Route::get('/mobile','HomeController@mobile');
    Route::get('/pc','HomeController@pc')->name('pc');//pc端
    Route::get('/mobile/msgbox','HomeController@msgbox');//
    Route::get('/common/api/msgbox','LayimController@msgbox');//消息列表
    Route::post('user/bind','LayimController@bind');//将client_id 与用户id绑定
    Route::post('layim/sendMessage','LayimController@sendMessage');//发送消息接口所有的消息都是通过此接口完成的
    Route::get('groups/{group}/members','LayimController@groupMembers');

    //搜索用户
    Route::get('user/api/search','UserController@search');
    Route::post('user/api/applyFriend','UserController@applyFriend');//添加好友
    Route::post('user/api/agreeFriend','UserController@agreeFriend');//同意添加好友
    Route::post('user/api/refuseFriend','UserController@refuseFriend');//拒绝添加好友
    Route::post('user/api/setSign','UserController@setSign');//设置签名
    Route::post('user/api/setStatus','UserController@setStatus');//设置在线状态
    Route::get('user/api/chatRecord','UserController@chatRecord');//聊天记录

    Route::get('group/api/search','GroupController@search');
    Route::post('group/api/applyGroup','GroupController@applyGroup');//添加群
    Route::post('group/api/agreeGroup','GroupController@agreeGroup');//同意添加群
    Route::post('group/api/refuseGroup','GroupController@refuseGroup');//拒绝添加群
    Route::get('group/api/getMembers','GroupController@getMembers');//获取群成员
    Route::post('groups','GroupController@store');//创建群

    Route::post('/upload/image','HomeController@uploadImage');//上传图片
    Route::post('/upload/file','HomeController@uploadFile');//上传文件

//    Route::middleware([])->get('/api/user', function () {
//
//        return ['name'=>'ddd','introduction'=>'','avatar'=>'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif','roles'=>['admin'],'csrfToken'=>csrf_token()];
//    });
//    Route::any('/{all?}','Controller@front')->where(['all'=>'.*']);
});

