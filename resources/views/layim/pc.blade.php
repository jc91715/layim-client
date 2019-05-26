@extends('layouts.layim')
@section('styles')
    <style>
        .members-box{
            padding: 10px;
        }
        .members{
            display: flex;
        }
        .img-box{
            width: 50px;
            height: 50px;
            border-radius: 30px;
            overflow: hidden;
        }
        .img-box img{
            width: 50px;
            height: 50px;
        }
    </style>
@endsection
@section('content')
   
    @endsection
@section('scripts')
    <script>


    </script>
    <script>
        layui.config({
            version: true
        }).use('layim', function(layim) {
            var userlist = JSON.parse('<?php echo json_encode($userlist); ?>');
            var wbsock_ip = '<?php echo $wbsocket_ip; ?>'
            var
            //     , layim = mobile.layim
            //     , layer = mobile.layer;
                 $ = layui.jquery;

            layim.config({
                isgroup:true,
                copyright:true,
                isNewFriend:true,
                init:userlist,

                //上传图片接口
                uploadImage: {
                    url: '/upload/image' //（返回的数据格式见下文）
                    ,type: '' //默认post
                }
                ,members: {
                    url: '/group/api/getMembers/'
                    ,data: {}
                }
                //上传文件接口
                ,uploadFile: {
                    url: '/upload/file' //（返回的数据格式见下文）
                    ,type: '' //默认post
                },
                msgbox: layui.cache.dir + 'css/modules/layim/html/msgbox.html' //消息盒子页面地址，若不开启，剔除该项即可
                ,find: layui.cache.dir + 'css/modules/layim/html/find.html' //发现页面地址，若不开启，剔除该项即可
                ,chatLog: layui.cache.dir + 'css/modules/layim/html/chatlog.html' //聊天记录页面地址，若不开启，剔除该项即可
                ,notice: true
            })

            layim.on('sendMessage', function(data){
                console.log(data)
                var send_data =  $.extend({content:data},{_token:Laravel.csrfToken})
                $.post('layim/sendMessage',send_data,function (response) {

                })
            });

            layim.on('sign', function(value){
                console.log(value); //获得新的签名

                //此时，你就可以通过Ajax将新的签名同步到数据库中了。

                if(value){
                    $.post('/user/api/setSign',{sign:value,_token:Laravel.csrfToken},function(){

                    })
                }
            });
            layim.on('online', function(status){

                $.post('/user/api/setStatus',{status:status,_token:Laravel.csrfToken},function(){

                })
                //此时，你就可以通过Ajax将这个状态值记录到数据库中了。
                //服务端接口需自写。
            });
            var socket = new WebSocket('ws://'+wbsock_ip+':8282');
            socket.onopen = function(){
                // socket.send('XXX连接成功');
                console.log('连接成功')
            };

            //监听收到的消息
            socket.onmessage = function(res){
                console.log('收到信息')
                console.log(res)

                var data = JSON.parse(res.data)
                switch (data.type) {
                    case 'login':
                        client_id = data.client_id
                        setInterval(function(){
                            socket.send('心跳')
                        },5000*10)

                        $.post('user/bind',{client_id:data.client_id,_token:Laravel.csrfToken},function (response) {
                        })
                        break;
                    case 'getMessage':
                        console.log('getMessage')
                        layim.getMessage(data['content'])
                        break;
                    case 'addList':
                        layim.addList(data['content'])
                        break;
                    case 'msgBox':
                        console.log('消息盒子')
                        console.log(data)
                        layim.msgbox(data['content'])
                        break;
                    case 'userStatus':
                        console.log('用户状态')
                        console.log(data)
                        layim.setFriendStatus(data['content']['id'], data['content']['status']);
                        break;
                }

                //res为接受到的值，如 {"emit": "messageName", "data": {}}
                //emit即为发出的事件名，用于区分不同的消息
            };



        })
    </script>
@endsection
